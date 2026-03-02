<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
//use Spatie\Permission\Traits\HasRoles;
use App\UserRoles;


class User extends Authenticatable
{
    use Notifiable, UserRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function employee(){

        if($this->user_type == 'employee')
        return $this->belongsTo('Modules\Masters\Entities\Employee','user_type_id');
        else
        return $this->belongsTo('Modules\Masters\Entities\Employee','user_type_id')->withDefault();
    }
}
