<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
            
    ];
  
    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
   
        /* define Gates */
		Gate::define('gate_create_user',function($user) {
			return $user->user_type == 'admin';

		});
		
		
		
		
		Gate::define('gate_get_users',function($user) {
			return $user->user_type == 'admin';
		});
		
		Gate::define('gate_get_clients',function($user) { 
			return ($user->user_type == 'admin' OR $user->user_type == "employee");
		});
		
		
		Gate::define('gate_create_investment',function($user) {
			return ($user->user_type == 'admin' OR $user->user_type == "employee");
		});
		
		Gate::define('gate_view_investment',function($user) {
			return ($user->user_type == 'admin' OR $user->user_type == "employee");
		});
		
		Gate::define('gate_create_document',function($user) {
			return ($user->user_type == 'admin' OR $user->user_type == "employee");
		});
		
		Gate::define('gate_get_document',function($user) {
			return ($user->user_type == 'admin' OR $user->user_type == "employee");
		});
		
		Gate::define('gate_get_scheme_dropdown',function($user) {
			return ($user->user_type == 'admin' OR $user->user_type == "employee");
		});
		
		Gate::define('gate_get_schemes',function($user) {
			return ($user->user_type == 'admin' OR $user->user_type == "employee");
		});
		
			
		Gate::define('gate_my_investment',function($user) {
			return $user->user_type == 'client';
		});
		
		Gate::define('gate_my_document',function($user) {
			return $user->user_type == 'client';
		});
		
		Gate::define('gate_user_removal',function($user) {
			return $user->user_type == 'admin';
		});
		
		
		//lets see if the logged in user authorized to do this operation..
		 if (!Gate::allows('')) {
			return response()->json(array('message'=>'You are not authorized to perform this action.', 'status' => "false"), 200);
		} 
		
		
		
		
		
		
		
	}
}


/*  <Button variant="link" onClick={() => {
			   this.selected_client = {id: cell,email:row.email,name: row.name};
				this.setState({action:{assign_mutual:true,id:cell}}) 
			}}>Create Investment</Button> */