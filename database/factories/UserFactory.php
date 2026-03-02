<?php

use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(App\User::class, function (Faker $faker) {
    return [
        'username' => 'admin',
        'email' => $faker->unique()->safeEmail,
        'user_type' => 'admin',
        'user_type_status' => 0,
        'default_role' => 1,
        'created_by' => 1,       
        'password' => Hash::make('12345'), // secret
        'remember_token' => str_random(10),
    ];
});
