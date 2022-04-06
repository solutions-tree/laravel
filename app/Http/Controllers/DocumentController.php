<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DocumentModel as Document;
use App\Http\Resources\DocumentResource;
use Illuminate\Support\Facades\Gate;

class DocumentController extends Controller
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
    public function createDocument(Request $request) {
       //

	  //Gates
	    if (!Gate::allows('gate_create_document')) {
			return response()->json(array('message'=>'You are not authorized to perform this action.', 'status' => "false"), 200);
		} 
	   
		// Validate incoming data..
		$validator = \Validator::make($request->all(),Document::getDataRules(), Document::getDataRulesMessages());
	 
		if($validator->fails()){
			 return response()->json($validator->messages(), 200);
		}
		
	/* 	if (!$request->hasFile('document_file') OR !$request->file('document_file')->isValid()) {
			//
			return response()->json(array('success' => "false", 'message' => "Please Upload the File." ), 200);
		}
		 */
		
		if (count($request->file('document_file'))>0) {
			
			foreach($request->file('document_file') as $key=>$val) {
				// insert data into database.
				$document_obj = new Document();
				$document_obj->document_name = $request->input("document_name");
				$document_obj->document_file = "";
				$document_obj->client_id = $request->input("client_id");
				$document_obj->document_description = $request->input("document_description");
				$document_obj->uploaded_by = $request->user()->id;
			
				if (!$document_obj->save()) {
					return response()->json(array('success' => false , 'message' => "Unable to save record in db."), 200);
				}
	
				$document_path = $val->storeAs(
						'document', $request->input("client_id") . "_" . uniqid() . "." .  $val->getClientOriginalExtension()
				);
					
				//$document_path = $request->document_file->store('document');
				if ($document_path) {
					$document_obj->id;
					$document_file_obj = Document::find($document_obj->id);
					$document_file_obj->document_file = $document_path;
					$document_file_obj->save();
				}
			}
		}
		
		
		return response()->json(array('status' => "true", 'message' => "Document Uploaded successfully"), 200);
		
	}
	
	public function getDocumentList(Request $request) {
		
		//lets build the vars...
		  //Gates
	    if (!Gate::allows('gate_get_document')) {
			return response()->json(array('message'=>'You are not authorized to perform this action.', 'status' => "false"), 200);
		} 
		
		
		$client_id = $request->input("client_id");
		$search_text = $request->input('search');
		$sort_order = (is_null($request->input('sort_order')) OR !in_array($request->input('sort_order'),['asc','desc']))?'asc':$request->input('sort_order');
		$sort_field = (is_null($request->input('sort_field')) && $request->input('sort_field')!="")?($request->input('sort_field')=="document_name")?"document_name":"document_name":'document_name';
		
	
		//return $query->where(DB::raw('CONCAT_WS(" ", "scheme_name", "scheme_category")'), 'like', "%"  . $search_text . "%");
		
		//DB::enableQueryLog(); // Enable query log
		
	$documents = DB::table('documents')->Where('client_id', '=',  $client_id)->when($search_text, function ($query, $search_text) {
		
							$search_text_data = explode(" ",$search_text);
							$db_return = [];
							foreach ($search_text_data as $key=>$val) {
								
							$db_return = $query->where(DB::raw('CONCAT(document_name, " " , document_description)'), 'like', "%"  . $val . "%");
							
							}
							
							return $db_return;
							
							//lets break the search text.. 
							
							//return $query->orWhere('scheme_name', 'REGEXP',  $search_string )->orWhere('scheme_category', 'REGEXP',  $search_string )->Where('scheme_nav_name', 'REGEXP',  $search_string);
							
							
						})->when([$sort_field,$sort_order], function ($query, $sort_data) {
							return $query->orderBy($sort_data[0], $sort_data[1]);
						})->paginate(10);
//

	return DocumentResource::collection($documents);
	}
	
	public function getMyDocumentList(Request $request) {
		 
		
		//lets build the vars...
		if (!Gate::allows('gate_my_document')) {
			return response()->json(array('message'=>'You are not authorized to perform this action.', 'status' => "false"), 200);
		} 
		
		
		$search_text = $request->input('search');
		$sort_order = (is_null($request->input('sort_order')) OR !in_array($request->input('sort_order'),['asc','desc']))?'asc':$request->input('sort_order');
		$sort_field = (is_null($request->input('sort_field')) && $request->input('sort_field')!="")?($request->input('sort_field')=="document_name")?"document_name":"document_name":'document_name';
		
	
		//return $query->where(DB::raw('CONCAT_WS(" ", "scheme_name", "scheme_category")'), 'like', "%"  . $search_text . "%");
		
		//DB::enableQueryLog(); // Enable query log
		
	$documents = DB::table('documents')->Where('client_id', '=',  $request->user()->id)->when($search_text, function ($query, $search_text) {
		
							$search_text_data = explode(" ",$search_text);
							$db_return = [];
							foreach ($search_text_data as $key=>$val) {
								
							$db_return = $query->where(DB::raw('CONCAT(document_name, " " , document_description)'), 'like', "%"  . $val . "%");
							
							}
							
							return $db_return;
							
							//lets break the search text.. 
							
							//return $query->orWhere('scheme_name', 'REGEXP',  $search_string )->orWhere('scheme_category', 'REGEXP',  $search_string )->Where('scheme_nav_name', 'REGEXP',  $search_string);
							
							
						})->when([$sort_field,$sort_order], function ($query, $sort_data) {
							return $query->orderBy($sort_data[0], $sort_data[1]);
						})->paginate(10);
//

	return DocumentResource::collection($documents);
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
