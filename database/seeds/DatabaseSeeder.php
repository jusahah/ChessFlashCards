<?php

use App\User;
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

        if (\App::environment('local')) {
          // Dev-login from UI is hard-coded to use this token
          // Speeds up things a bit in dev
          $jussiToken = '123456';
        } else {
          $jussiToken = null;
        }
        
        User::create([
          'name' => 'Jussi',
          'email' => 'jussiahamalainen@gmail.com',
          'api_token' => $jussiToken,
          'role' => 'admin',
        ]);

    }
}
