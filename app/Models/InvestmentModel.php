<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class InvestmentModel extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $table = 'investment';
	
    
	protected $fillable = [
        'scheme_code',
        'client_id',
        'investment_amt',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    /*protected $hidden = [
        'password',
        'remember_token',
    ];
	*/
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    /* protected $casts = [
        'email_verified_at' => 'datetime',
    ]; */
	
	public static function getDataRules() {
			
		return [
		'scheme_code' => ['required'],
		'client_id' => ['required'],
		'investment_amt' => ['required'],
		//'investment_date' => ['required']
		];
		
	}
	
	public static function getDataRulesMessages() {
		return [
			'scheme_code.required' => 'Scheme is required',
			'client_id.required' => 'Client is required.',
			'investment_amt.required' => 'Investment Amount is required.',
			'investment_date.required' => 'Investment date is required.'
		];
    }
}
