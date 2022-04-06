<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\MutualFundResource;
use Illuminate\Support\Facades\Gate;


class MutualFund extends Controller
{
    //method to pull user from the database...
	public function getSchemes(Request $request) {
		
		//return $request->all();
	    if (!Gate::allows('gate_get_schemes')) {
			return response()->json(array('message'=>'You are not authorized to perform this action.', 'status' => "false"), 200);
		} 
		
		//lets build the vars...
		$search_text = $request->input('search');
		$sort_order = (is_null($request->input('sort_order')) OR !in_array($request->input('sort_order'),['asc','desc']))?'asc':$request->input('sort_order');
		$sort_field = (is_null($request->input('sort_field')) && $request->input('sort_field')!="")?($request->input('sort_field')=="scheme_code")?"code":"scheme_name":'scheme_name';
		
	
		//return $query->where(DB::raw('CONCAT_WS(" ", "scheme_name", "scheme_category")'), 'like', "%"  . $search_text . "%");
		
		//DB::enableQueryLog(); // Enable query log
		
	$mf_schemes = DB::table('mutual_fund_list')->when($search_text, function ($query, $search_text) {
		
							$search_text_data = explode(" ",$search_text);
							$db_return = [];
							foreach ($search_text_data as $key=>$val) {
								
							$db_return = $query->where(DB::raw('CONCAT(scheme_name, " " , scheme_category , " " , scheme_nav_name)'), 'like', "%"  . $val . "%");
							
							}
							
							return $db_return;
							
							//lets break the search text.. 
							
							//return $query->orWhere('scheme_name', 'REGEXP',  $search_string )->orWhere('scheme_category', 'REGEXP',  $search_string )->orWhere('scheme_nav_name', 'REGEXP',  $search_string);
							
							
						})->when([$sort_field,$sort_order], function ($query, $sort_data) {
							return $query->orderBy($sort_data[0], $sort_data[1]);
						})->paginate(10);
//

	return MutualFundResource::collection($mf_schemes);
	
	}
	
	    //method to pull user from the database...
	public function getSchemeDropdown(Request $request) {
		
			//return $request->all();
	    if (!Gate::allows('gate_get_scheme_dropdown')) {
			return response()->json(array('message'=>'You are not authorized to perform this action.', 'status' => "false"), 200);
		} 
		
		
		//lets build the vars...
		$search_text = $request->input('search');
		
		
	$mf_schemes = DB::table('mutual_fund_list')->when($search_text, function ($query, $search_text) {
		
							$search_text_data = explode(" ",$search_text);
							$db_return = [];
							foreach ($search_text_data as $key=>$val) {
								
							$db_return = $query->where(DB::raw('CONCAT(code, " " , scheme_name, " " , scheme_category , " " , scheme_nav_name)'), 'like', "%"  . $val . "%");
							
							}
							
							return $db_return;
							
							//lets break the search text.. 
							
							//return $query->orWhere('scheme_name', 'REGEXP',  $search_string )->orWhere('scheme_category', 'REGEXP',  $search_string )->orWhere('scheme_nav_name', 'REGEXP',  $search_string);
							
							
						})->paginate(10);
//

	return MutualFundResource::collection($mf_schemes);
	
	}
	
	
}
