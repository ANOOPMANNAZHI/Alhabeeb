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
     * Staff a message may go to: an active login and, for an employee login, an active
     * employee record (employees.employee_status = 1). Used by Mail -> Compose and Send (user, 07/10/2026).
     */
    public function scopeActiveStaff($query)
    {
        return $query->where('user_type_status', TRUE)->where(function ($q) {
            $q->where('user_type', '<>', 'employee')
              ->orWhereIn('user_type_id', function ($e) {
                  $e->select('id')->from('employees')->where('employee_status', 1);
              });
        });
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

    /**
     * Login page "Forgot password": the system template password_forgot
     * (Mail -> Templates) instead of Laravel's built-in notification.
     */
    public function sendPasswordResetNotification($token)
    {
        \Modules\BackOffice\Services\AccountMessages::forgot($this, $token);
    }

}
