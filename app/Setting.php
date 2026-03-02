<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Spatie\Permission\Models\Role;
use App\UserRoles;

class Setting extends Model
{
    protected $guarded = [];
    protected $fillable = [];
    protected $table = 'configuration';

}
