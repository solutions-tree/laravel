<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;
use App\Http\Resources\UserRelationshipResource;
use IIlluminate\Support\Facades\Storage;


class UserController extends Controller{
    
	// function to signup..
	
	function SignUp(Request $request) {
		
		// Validate incoming data..
		$validator = Validator::make($request->all(),[
		'user_type' => ['in:client,employee','required'],
		'first_name' => ['required'],
		'last_name' => ['required'],
		'email' => ['required','unique:users,email'],
		'password' => ['required']
		],[
			'user_type.in' => 'User Type is not valid',
			'user_type.required' => 'User type is required',
			'first_name.required' => 'First Name is required.',
			'last_name.required' => 'Last Name is required.',
			'email.required' => 'Email is required.',
			'email.unique' => 'Email Address already exists.'
		]);
	 
		if($validator->fails()){
			 return response()->json(["message" => $validator->messages()], 200);
		}
		
		
		// insert data into database.
		$user = new User();
		$user->plain_password = $request->input("password");
		$user->password = bcrypt($request->input("password"));
		$user->first_name = $request->input("first_name");
		$user->last_name = $request->input("last_name");
		$user->email = $request->input("email");
		$user->user_type = (strtolower($request->input("user_type"))=="team")?"employee":"client";
		$user->phone = (is_null($request->input("phone")))?"":$request->input("phone");
		$user->address1 = "";
		$user->address2 = "";
		
		if (!$user->save()) {
			return response()->json(array('status' => false), 200);
		}
		
		event(new Registered($user));
		return response()->json(array('status' => "true", 'user_created' => 1), 200);
		
	}
	  
	
	
	// method to create the user ....
	function createUser (Request $request) {
		
		
		// gate 
		if (!Gate::allows('gate_create_user')) {
			return response()->json(array('message'=>'You are not authorized to perform this action.', 'status' => "false"), 200);
		} 
		
		// Validate incoming data..
		$validator = Validator::make($request->all(), User::getDataRules(), User::getDataRulesMessages());
	 
		if($validator->fails()){
			 return response()->json($validator->messages(), 200);
		}
		
		
		
		// insert data into database.
		$plain_password = Str::random(9);
		$user = new User();
		$user->plain_password = $plain_password;
		$user->password = bcrypt($plain_password);
		$user->first_name = $request->input("first_name");
		$user->last_name = $request->input("last_name");
		$user->email = $request->input("email");
		$user->user_type = $request->input("user_type");
		$user->phone = (is_null($request->input("phone")))?"":$request->input("phone");
		$user->address1 = (is_null($request->input("address1")))?"":$request->input("address1");
		$user->address2 = (is_null($request->input("address2")))?"":$request->input("address2");
		
		if (!$user->save()) {
			return response()->json(array('success' => false), 200);
		}
		event(new Registered($user));
		return response()->json(array('success' => "true", 'user_created' => 1), 200);
		
	}
	
	//method to pull user from the database...
	public function getUsers(Request $request) {
		
		// gate 
		 if (!Gate::allows('gate_get_users')) {
			return response()->json(array('message'=>'You are not authorized to perform this action.', 'status' => "false"), 200);
		  } 
		
			//lets build the vars...
		$search_text = $request->input('search');
		$sort_order = (is_null($request->input('sort_order')) OR !in_array($request->input('sort_order'),['asc','desc']))?'desc':$request->input('sort_order');
		$sort_field = (is_null($request->input('sort_field')) && $request->input('sort_field')!="")?($request->input('sort_field')=="created_at")?"created_at":"created_at":'created_at'; 
		//DB::enableQueryLog(); // Enable query log
		
	$users = DB::table('users')->Where('id', '!=',  $request->user()->id)->when($search_text, function ($query, $search_text) {
							return $query->where('first_name', 'like', "%"  . $search_text . "%");
						})->when([$sort_field,$sort_order], function ($query, $sort_data) {
							return $query->orderBy($sort_data[0], $sort_data[1]);
						})->paginate(10);

		//return $request->user();
		return UserResource::collection($users);
	}
	
	// get clients 
	function getClients(Request $request) {
		
			// gate 
		if (!Gate::allows('gate_get_clients')) {
			return response()->json(array('message'=>'You are not authorized to perform this action.', 'status' => "false"), 200);
		} 
		
		//lets build the vars..
		$user_type =  $request->user()->user_type;
		$user_id = $request->user()->id;
		//print_r($request->user());
		
		$search_text = $request->input('search');
		$sort_order = (is_null($request->input('sort_order')) OR !in_array($request->input('sort_order'),['asc','desc']))?'desc':$request->input('sort_order');
		$sort_field = (is_null($request->input('sort_field')) && $request->input('sort_field')!="")?($request->input('sort_field')=="created_at")?"created_at":"created_at":'created_at'; 
		//DB::enableQueryLog(); // Enable query log
		
		$users = DB::table('users as c')->leftjoin('client_manager as cm', 'c.id', '=', 'cm.client_id')->leftjoin('users as rm', 'cm.employee_id', '=', 'rm.id')->select('c.*', 'rm.first_name as rm_fname', 'rm.last_name as rm_lname', 'cm.employee_id')->where('c.user_type', 'client')->when($search_text, function ($query, $search_text) {
							return $query->where('c.first_name', 'like', "%"  . $search_text . "%");
						})->when([$sort_field,$sort_order], function ($query, $sort_data) {
							return $query->orderBy($sort_data[0], $sort_data[1]);
						})->when($user_type!="admin",function($query) use($user_id) {
								return $query->where('cm.employee_id', '=' , $user_id);
						})->paginate(10);

		//return $request->user();
		return UserRelationshipResource::collection($users);
	}
	// managers:
	
	public function getManagers(Request $request) {
		
		$users = DB::table('users')->where('user_type', '!=' , 'client')->get();
		
		return UserResource::collection($users);
		
	}
	
	public function assignManager(Request $request) {
		
		/* $relation_response = DB::table('client_manager')->insert([
			'client_id' => $request->input('client_id'),
			'employee_id' => $request->input('manager_id'),
			"created_at" =>  \Carbon\Carbon::now(), # new \Datetime()
            "updated_at" => \Carbon\Carbon::now()  # new \Datetime()
		]); */
		
		$relation_response = DB::table('client_manager')->upsert(
			['client_id' => $request->input('client_id'),
			'employee_id' => $request->input('manager_id'),
			"created_at" =>  \Carbon\Carbon::now(), # new \Datetime()
            "updated_at" => \Carbon\Carbon::now()  # new \Datetime()
			],['client_id'],['employee_id']
		);

		
		return response()->json([
		
				'data' => $relation_response,
						'status' => 'true'
						   ], 200);
		
	}
	
	
	//get my profile
	public function getProfile(Request $request) {
		$user_data = [
		 'email' => $request->user()->email,
		 'first_name' => $request->user()->first_name,
		 'last_name' => $request->user()->last_name,
		 'address1' => $request->user()->address1,
		 'address2' => $request->user()->address2,
		 'phone' => $request->user()->phone,
		];
		
		return response()->json([
				'data' => $user_data,
						'status' => 'true'
						   ], 200);
	}
	
	//lets inactivate the user ..
	public function handleActivation(Request $request) {
		
		//lets see if the logged in user authorized to do this operation..
		 if (!Gate::allows('gate_user_removal')) {
			return response()->json(array('message'=>'You are not authorized to perform this action.', 'status' => "false"), 200);
		} 
		
		//lets inactivate the user..
		$user_id_to_archive = $request->input("user_id");
		$user_act_type = $request->input("act_type");
		
		//lets check, if someone trying to delete the admin...
		$user = Auth::user();
		if ($user->id == $user_id_to_archive) {
			return response()->json(array('message'=>'Admin can not be deleted', 'status' => "error"), 200);
		}
		
		
		DB::table('users')
              ->where('id', $user_id_to_archive)
              ->update(['is_active' => ($user_act_type=="act")?"yes":"no" ]);
		$label = ($user_act_type=="act")?"Activated":"Deactivated";
		return response()->json(array('status' => "success", 'message' => 'User ' .  $label . ' succesfully'), 200);		
		
	}
	
	//lets delete the user...
	public function deleteUser(Request $request) {
		
		//lets see if the logged in user authorized to do this operation..
		 if (!Gate::allows('gate_user_removal')) {
			return response()->json(array('message'=>'You are not authorized to perform this action.', 'status' => "error"), 200);
		} 
		
		$user_id_to_delete = $request->input("user_id");
		
		//lets check, if someone trying to delete the admin...
		$user = Auth::user();
		if ($user->id == $user_id_to_delete) {
			return response()->json(array('message'=>'Admin can not be deleted', 'status' => "error"), 200);
		}
		
		
		//lets check if user is employee and he is currently having any created entry in investment or document table..
		$user_data = DB::table('users')->where('id', '=', $request->input("user_id"))->first();
		
		//lets check if user is employee type..
		if ($user_data->user_type =="employee") {
				
				//
				$rel_count = 0;
				$rel_count = DB::table('client_manager')->where('employee_id' , '=' , $user_data->id)->count();
				
				if ($rel_count>0) {
					return response()->json(array('message'=>"Can not deleted!. This User(employee) is already assigned to a client", 'status' => "error"), 200);
				} 
				
				$rel_count = DB::table('investment')->where('created_by_id' , '=' , $user_data->id)->count();
				
				if ($rel_count>0) {
					return response()->json(array('message'=>"Can not deleted!. This User(employee) already holds " .$rel_count . " investments created for clients", 'status' => "error"), 200);
				} 
				
				$rel_count = DB::table('documents')->where('uploaded_by' , '=' , $user_data->id)->count();
				
				if ($rel_count>0) {
					return response()->json(array('message'=>"Can not deleted!. This User(employee) already holds " .$rel_count . " documents created for clients", 'status' => "error"), 200);
				} 
				
				
				// now lets delete the employee only..
				//lets delete user first..
				DB::table('users')->where('id', '=', $user_data->id)->delete();
				return response()->json(array('message'=>'User deleted succesfully', 'status' => "true"), 200);
				
				
				
		}
		
		
		
		
		DB::beginTransaction();
		try {
			//lets pic all the document files first...
			$documents =  DB::table('documents')->select('document_file')
                ->where('client_id', '=', $user_id_to_delete)
                ->get();
			
			//lets delete user first..
			DB::table('users')->where('id', '=', $user_id_to_delete)->delete();
			

			//lets delete user all relationship managers..
			DB::table('client_manager')->where('client_id', '=', $user_id_to_delete)->delete();
			
			//lets delete user all investments.
			DB::table('investment')->where('client_id', '=', $user_id_to_delete)->delete();
			
			
			//lets delete user all documents...
			DB::table('documents')->where('client_id', '=', $user_id_to_delete)->delete();
			

			DB::commit();
			
			//if things go ok.. lets delete the files...
			foreach ($documents as $document) {
				Storage::delete('/'. $document->document_file);
			}
			
			return response()->json(array('message'=>'User deleted succesfully', 'status' => "true"), 200);
			
			
		} catch (\Exception $e) {
			DB::rollback();
		}

	}
	
	
	public function getUserDetail(Request $request) {
		
		$user_data = DB::table('users')->where('id', '=', $request->input("user_id"))->first();
		$data = [
			'first_name' => $user_data->first_name,
			'last_name' => $user_data->last_name,
			'email' => $user_data->email,
			'user_type' => $user_data->user_type,
			'address1' => $user_data->address1,
			'address2' => $user_data->address2,
			'phone' => $user_data->phone,
			'is_active' => $user_data->is_active
			
		];
		
		return response()->json([
				'data' => [$data],
						'status' => 'true'
						   ], 200);
	}
	
	
	function updateProfile(Request $request) {
		
		$user = Auth::user();
		$validator = Validator::make($request->all(), [
			'first_name' => ['required'],
			'last_name' => ['required']
		],[
			'first_name.required' => 'First Name is required.',
			'last_name.required' => 'Last Name is required.',
		]);
	 
		if($validator->fails()){
			 return response()->json($validator->messages(), 200);
		}
		$user->first_name = $request->input('first_name');
		$user->last_name = $request->input('last_name');
		$user->address1 = $request->input('address1');
		$user->address2 = $request->input('address2');
		$user->phone = $request->input('phone');
		
		if (!$user->save()) {
			return response()->json(array('message'=>'Unable to update profile', 'status' => "false"), 200);
		}
		
		return response()->json(array('status' => "true", 'message' => 'Your Profile Updated succesfully'), 200);
	}
	
	function changePassword(Request $request) {
		
		$user = Auth::user();
		$validator = Validator::make($request->all(), [
			'old_password' => ['required'],
			'new_password' => ['required']
		],[
			'old_password.required' => 'Old password is required.',
			'last_name.required' => 'New Password is required.',
		]);
		
		if($validator->fails()){
			 return response()->json($validator->messages(), 200);
		}
	  
	  // check current password 
      if(Hash::check($request->input('old_password'), $user->password)) {           
        $user->password = Hash::make($request->input('new_password'));
        
		if (!$user->save()) {
			return response()->json(array('message'=>'Unable to change password', 'status' => "false"), 200);
		} 
		
        return response()->json(array('status' => "true", 'message' => 'Your Password changed successfully'), 200);
      } else {
		  
		 return response()->json(array('message'=>'Old/Current password is incorrect.', 'status' => "false"), 200); 
		  
	  }
		
	}
	
	
}
