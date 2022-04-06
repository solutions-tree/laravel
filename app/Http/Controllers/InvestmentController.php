<?php

namespace App\Http\Controllers;

use App\Models\InvestmentModel as Investment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\InvestmentResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;

class InvestmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Create an Investment
     *
     * @return \Illuminate\Http\Response
     */
    public function createInvestment(Request $request) {
       //

	   //return $request->all();
	   if (!Gate::allows('gate_create_investment')) {
			return response()->json(array('message'=>'You are not authorized to perform this action.', 'status' => "false"), 200);
		} 
	  
	   
		// Validate incoming data..
		$validator = \Validator::make($request->all(), Investment::getDataRules(), Investment::getDataRulesMessages());
	 
		if($validator->fails()){
			 return response()->json($validator->messages(), 200);
		}
		
		
		// insert data into database.
		$investment_obj = new Investment();
		$investment_obj->scheme_code = $request->input("scheme_code");
		$investment_obj->scheme_id = $request->input("scheme_id");
		$investment_obj->client_id = $request->input("client_id");
		$investment_obj->folio_number = $request->input("folio_number");
		$investment_obj->investment_unit = $request->input("investment_unit");
		$investment_obj->investment_amt = (float) $request->input("investment_amt");
		$investment_obj->investment_date =   date('Y-m-d' , strtotime($request->input("investment_date")));
		$investment_obj->created_by_id = $request->user()->id;
		//$investment_obj->created_date = $request->input("date");
		//$investment_obj->insert_date = date();
		
		if (!$investment_obj->save()) {
			return response()->json(array('success' => false), 200);
		}
		
		// lets do one thing now.. lets update the same scheme code id...
		$this->updateNavToScheme($request->input("scheme_code"));
		
		return response()->json(array('status' => "true", 'message' => "Investment Created successfully"), 200);
		
	}
	
	
	function updateNavToScheme($scheme_code) {
		
		if (!$scheme_code) return;
		
		//lets find out if the nav already in db or not.. 
		$mutual_fund = DB::table('mutual_fund_list')->Where('code', '=',  $scheme_code)->whereNull('nav')->first();
		
		// lets see, if we do not get any fund value...
		if ($mutual_fund) {
				 
			//lets fetch from the remote nav server and update it..
				$response = Http::get('https://api.mfapi.in/mf/' . $scheme_code);
	
				// lets get the latest value ...
				$data = $response->json();
				
				if (isset($data['data'][0])) {
					
					//lets update the memory with nav and its date...
					$res = DB::table('mutual_fund_list')->where('id', $mutual_fund->id)->update([
						'nav' => (float) $data['data'][0]['nav'],
						'nav_updated_at' => date('Y-m-d' , strtotime($data['data'][0]['date']))
					]);
					
					
				}
			
		}
	}
	
	public function viewInvestmentByClientId(Request $request) {
		
				//lets build the vars...
		if (!Gate::allows('gate_view_investment')) {
			return response()->json(array('message'=>'You are not authorized to perform this action.', 'status' => "false"), 200);
		} 
		
		
		$client_id = $request->input("client_id");
		$search_text = $request->input('search');
		$sort_order = (is_null($request->input('sort_order')) OR !in_array($request->input('sort_order'),['asc','desc']))?'asc':$request->input('sort_order');
		$sort_field = (is_null($request->input('sort_field')) && $request->input('sort_field')!="")?($request->input('sort_field')=="scheme_code")?"code":"investment_date":'investment_date';
		
	
		//return $query->where(DB::raw('CONCAT_WS(" ", "scheme_name", "scheme_category")'), 'like', "%"  . $search_text . "%");
		
		//DB::enableQueryLog(); // Enable query log
		
	$investments = DB::table('investment')->join('mutual_fund_list', 'code', '=', 'scheme_code')->Where('client_id', '=',  $client_id)->when($search_text, function ($query, $search_text) {
		
							$search_text_data = explode(" ",$search_text);
							$db_return = [];
							foreach ($search_text_data as $key=>$val) {
								
							$db_return = $query->where(DB::raw('CONCAT(scheme_code, " " , investment_date)'), 'like', "%"  . $val . "%");
							
							}
							
							return $db_return;
							
							//lets break the search text.. 
							
							//return $query->orWhere('scheme_name', 'REGEXP',  $search_string )->orWhere('scheme_category', 'REGEXP',  $search_string )->Where('scheme_nav_name', 'REGEXP',  $search_string);
							
							
						})->when([$sort_field,$sort_order], function ($query, $sort_data) {
							return $query->orderBy($sort_data[0], $sort_data[1]);
						})->paginate(10);
//

	return InvestmentResource::collection($investments);
	}
	
	public function getMyInvestmentList(Request $request) {
		
		//lets build the vars...
		if (!Gate::allows('gate_my_investment')) {
			return response()->json(array('message'=>'You are not authorized to perform this action.', 'status' => "false"), 200);
		} 
		
		$search_text = $request->input('search');
		$sort_order = (is_null($request->input('sort_order')) OR !in_array($request->input('sort_order'),['asc','desc']))?'asc':$request->input('sort_order');
		$sort_field = (is_null($request->input('sort_field')) && $request->input('sort_field')!="")?($request->input('sort_field')=="scheme_code")?"code":"investment_date":'investment_date';
		
	
		//return $query->where(DB::raw('CONCAT_WS(" ", "scheme_name", "scheme_category")'), 'like', "%"  . $search_text . "%");
		
		//DB::enableQueryLog(); // Enable query log
		
	$investments = DB::table('investment')->join('mutual_fund_list', 'code', '=', 'scheme_code')->Where('client_id', '=',  $request->user()->id)->when($search_text, function ($query, $search_text) {
		
							$search_text_data = explode(" ",$search_text);
							$db_return = [];
							foreach ($search_text_data as $key=>$val) {
								
							$db_return = $query->where(DB::raw('CONCAT(scheme_code, " " , investment_date)'), 'like', "%"  . $val . "%");
							
							}
							
							return $db_return;
							
							//lets break the search text.. 
							
							//return $query->orWhere('scheme_name', 'REGEXP',  $search_string )->orWhere('scheme_category', 'REGEXP',  $search_string )->Where('scheme_nav_name', 'REGEXP',  $search_string);
							
							
						})->when([$sort_field,$sort_order], function ($query, $sort_data) {
							return $query->orderBy($sort_data[0], $sort_data[1]);
						})->paginate(10);
//

	return InvestmentResource::collection($investments);
	}
	
	
	
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
