<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class PreferredMails extends Model
{
	
    protected $fillable = [];
    protected $guarded = [];
    protected $table = 'preferred_mails';

    public function user(){
      return $this->belongsTo('App\User','user_id','id');
    } 

    public function massMail(){
    	 return $this->belongsTo('Modules\Masters\Entities\MassMails','mass_mail_id');
    } 
    public function getStatusNameAttribute()
    {     
        switch($this->status){
          case '1' : return 'Pending';
          case '0' : return 'Failed';        
          case '2' : return 'Success';        
        }
    }
    public function createdBy(){
      return $this->belongsTo('App\User','created_by','id');
    }
}
