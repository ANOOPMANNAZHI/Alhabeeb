<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
//use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use App\UserRoles;


class User extends Authenticatable
{
    use Notifiable, UserRoles;
	protected $dates = ['user_last_login'];
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
        
        return $this->belongsTo('Modules\Masters\Entities\Employee','user_type_id')->withDefault();
        
     }


    public function getDefaultRoleNameAttribute()
    {

        $role =  Role::select('name')->find($this->default_role);
        return $role->name;

    }
    /*
    *  Status - Active
    *
    */
    public function scopeActive($query)
    {
        return $query->where('user_type_status', TRUE);
    }
	
    /**
     * Get all of the user Roles
     */
    public function userRoles()
    {   
        return $this->belongsToMany('Spatie\Permission\Models\Role', 'model_has_roles', 'model_id', 'role_id');
        
    }
	
	public function Are(){
        return $this->hasOne('Modules\Masters\Entities\AreBuildingAssign','user_id','id'); 
     }


}
