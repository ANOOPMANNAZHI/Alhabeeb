<?php

namespace App;
 
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Support\Collection;


trait UserRoles
{

 use HasRoles;

/*
*  Get Role Id 
*
**/
    public function getRoles(): Collection
    {
        return $this->roles->pluck('id');
        
    }
    /*
    * Get RoleName
    *
    */
    public function getUserRoleNames(): Collection
    {
        return $this->roles->pluck('name');
        
    }

    /*
    *  Sales User Roles
    */
    public function salesUsersRoles() {
           return $this->belongsToMany('\Modules\Sales\Entities\Sales', 'sales_users','role_id', 
            'sales_id');
     }


 
}

