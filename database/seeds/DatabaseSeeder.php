<?php

use Illuminate\Database\Seeder;


class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
      //  $this->call(UsersTableSeeder::class);
    //	factory(App\User::class,1)->create(); 

       DB::table('contract_renewal_types')->insert([ 
        [
        'type' => 'Normal Renewal',
        'created_at' => now(),
        'updated_at' => now()
       ],
       [
        'type' => 'Short Term Contract',
        'created_at' => now(),
        'updated_at' => now()
       ],
       [
        'type' => 'Rent Reduction',
        'created_at' => now(),
        'updated_at' => now()
       ],
       [
        'type' => 'Others',
        'created_at' => now(),
        'updated_at' => now()
       ]
     ]);

     /*  DB::table('discussion_categories')->insert([ 
        [
        'category' => 'Rent Reduction',
        'created_at' => now(),
        'updated_at' => now()
       ],[
        'category' => 'Short Term Contract',
        'created_at' => now(),
        'updated_at' => now()
       ],[
        'category' => 'Vacating',
        'created_at' => now(),
        'updated_at' => now()
       ],[
        'category' => 'Pre-mature',
        'created_at' => now(),
        'updated_at' => now()
       ],[
        'category' => 'Others',
        'created_at' => now(),
        'updated_at' => now()
       ]
       
      ]);
*/

       // DB::table('preferred_time')->insert([ 
       //  'time' => '8-1',
       //  'created_at' => now(),
       //  'updated_at' => now(),
       //   ]);

       // DB::table('preferred_time')->insert([ 
       //  'time' => '2-5',
       //  'created_at' => now(),
       //  'updated_at' => now(),
       //   ]); 

       // DB::table('preferred_time')->insert([ 
       //   'time' => 'After 5', 
       //  'created_at' => now(),
       //  'updated_at' => now(),
       //   ]);

         
       



    }
}
