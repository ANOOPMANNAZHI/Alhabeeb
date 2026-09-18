<?php

namespace Modules\BackOffice\Entities;

use Illuminate\Database\Eloquent\Model;

class MassMails extends Model
{
    use \App\Concerns\LegacyDateSerialization;
    protected $guarded = [];
    protected $table = 'mass_mails';
    protected $casts = ['created_at' => 'datetime'];
    /*
    * Preferred Mails -To
    *
    **/
    public function preferredMailsTo(){

       return $this->hasMany('Modules\BackOffice\Entities\PreferredMails','mass_mail_id','id')->where('mail_type',1);
    }
    /*
    * Preferred Mails -Cc
    *
    **/
    public function preferredMailsCc(){

       return $this->hasMany('Modules\BackOffice\Entities\PreferredMails','mass_mail_id','id')->where('mail_type',0);
    }
     /*
    * Preferred Mails -Cc
    *
    **/
    public function tenantMails(){

       return $this->belongTo('Modules\Sales\Entities\Tenant','user_id','id');
    }
     /*
    * Preferred Mails -Cc
    *
    **/
    public function vendorMails(){

       return $this->belongTo('Modules\Masters\Entities\Vendor','user_id','id');
    }
    
}
