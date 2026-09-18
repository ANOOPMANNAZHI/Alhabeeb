<?php

namespace Modules\Masters\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role;
use App\User;
use Kyslik\ColumnSortable\Sortable;

class employee extends Model
{
    use \App\Concerns\LegacyDateSerialization;
	use SoftDeletes, Sortable;

    protected $guarded = [];
    
    protected $table = 'employees';

    protected $casts = ['deleted_at' => 'datetime', 'created_at' => 'datetime', 'updated_at' => 'datetime', 'employee_dob' => 'datetime'];
    
    public $sortable = ['id','employee_code','employee_name','employee_contact_no','employee_status'];
    
    public function setEmployeeDobAttribute($value)
    {

        $this->attributes['employee_dob'] = date('Y-m-d', strtotime($value));
    }

    public function designations(){

    return $this->hasOne(Designation::class,'id','designation_id');
    }

    public function roles(){

        return $this->hasOne(Role::class,'id','role_id');
    }

    public function user(){

        return $this->hasOne(User::class,'user_type_id','id')->where('user_type','employee');
    }
    /*
    *  Status - Active
    *
    */
    public function scopeActive($query)
    {
        return $query->where('employee_status', 1);
    }
    /*
    *
    *
    * Head Role
    *
    */
    public function headRole(){

        return $this->hasOne(Role::class,'id','head_role');
    }
    /*
    *
    *
    * Head User
    *
    */
    public function headUser(){

        return $this->hasOne(User::class,'id','head_user')->where('user_type','employee');
    }
    /*
    *
    *
    * Head By User
    *
    */
    public function headByUser(){

        return $this->hasMany(User::class,'id','head_user')->where('user_type','employee');
    }
    /*
    *
    *
    * Job Category
    *
    */
    public function job(){

        return $this->hasOne(JobCategory::class,'id','job_category_id');
    }
}
