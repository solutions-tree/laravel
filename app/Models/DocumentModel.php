<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

class DocumentModel extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
	protected $table = 'documents';
	
    
	protected $fillable = [
        'document_name',
        'client_id',
        'document_file',
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
			
		return  [
		'document_name' => ['required'],
		'client_id' => ['required']
		];
		
	}
	
	public static function getDataRulesMessages() {
		return  [
			'document_name.required' => 'Document Name is required one',
			'client_id.required' => 'Client information is missing'
		];
    }
}
