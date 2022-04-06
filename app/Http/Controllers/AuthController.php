<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller {
	
    public function authenticate(Request $request) {
		
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
			
			$user = Auth::user();
			
			if ($user->hasVerifiedEmail()) {
					$request->session()->regenerate();
			
			//\Cookie::queue('is_logged_in', 'true');
			
			//response()->withCookie(cookie('is_logged_in', 'true'));
			
					 return response()->json([
			'message' => 'Login Succesfull',
			'status' => 'true'
					   ], 200)->withCookie(\Cookie::make('laravel_udd', base64_encode('true'), 0, null, null, false, false))->withCookie(\Cookie::make('laravel_ut', ($user->user_type), 0, null, null, false, false))->withCookie(\Cookie::make('logged_in_user_name', ($user->first_name), 0, null, null, false, false));
					   
					   
			} else  {
						return response()->json([
				'message' => 'Please check your email inbox and click on activation link to verify your account first.',
						'status' => 'false'
						   ], 200);
				
			}
			
			
                    
		} else {
			
			return response()->json([
'message' => 'Invalid login details',
		'status' => 'false'
           ], 200);
		}
	}
	
	function verifyEmail(Request $request) {
		$user = User::find($request->route('id'));
		if ($request->hasValidSignature(false) == 1) {
			if ($user->hasVerifiedEmail()) {
				return response()->json([
			'message' => 'Your account is already verified',
					'status' => 'false'
					   ], 200);
			}
				
			if ($user->markEmailAsVerified()) {
				return response()->json([
				'message' => 'Congratulations!. Your Account is successfully verified. You are recommended to change your Account Password.',
						'status' => 'true'
						   ], 200);
			} else  {
				return response()->json([
			'message' => 'Issue with Account verification',
					'status' => 'false'
					   ], 200);
			}
		} else {
			return response()->json([
			'message' => 'The verification url does not exist anymore',
					'status' => 'false'
			], 200);
		}
	}
	
	public function Logout(Request $request) {
		
		$request->session()->flush();
		return response()->json([
		'message' => 'Logout Succesfull'
				   ], 200)->withCookie( \Cookie::forget('laravel_udd'));
				   		
	}
}
