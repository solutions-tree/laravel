<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Auth\Notifications\VerifyEmail as VerifyEmailBase;
use Illuminate\Support\Facades\URL;
use Illuminate\Notifications\Messages\MailMessage;
use Carbon\Carbon;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	public $plain_password;
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
	
	public static function getDataRules() {
			
		return [
		'user_type' => ['required'],
		'first_name' => ['required'],
		'last_name' => ['required'],
		'email' => ['required']
		];
		
	}
	
	public function sendEmailVerificationNotification() {
		
		$this->notify(new VerifyEmail());
	
	}

	
	public static function getDataRulesMessages() {
		return [
			'user_type.required' => 'User type is required.',
			'first_name.required' => 'First Name is required.',
			'last_name.required' => 'Last Name is required.',
			'email.required' => 'Email is required.'
		];
    }
	
}



class VerifyEmail extends VerifyEmailBase
{
//    use Queueable;

    /**
     * Get the verification URL for the given notifiable.
     *
     * @param  mixed  $notifiable
     * @return string
     */
    protected function verificationUrl($notifiable)
    {
	
        $prefix = "http://localhost:3000";
        $temporarySignedURL = URL::temporarySignedRoute(
            'verification.verify', Carbon::now()->addMinutes(60), ['id' => $notifiable->getKey(),'hash' => sha1($notifiable->getEmailForVerification())],false
        );

        // I use urlencode to pass a link to my frontend.
        return $prefix . $temporarySignedURL ;
    }
	
	public function toMail($notifiable) {
		$verificationUrl = $this->verificationUrl($notifiable);
		return (new MailMessage)->view('emailverify', ['url' => $verificationUrl , 'password' => $notifiable->plain_password]);
	} 
	
	
	
	
}

