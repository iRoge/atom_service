<?php

use Illuminate\Database\Seeder;

class UserAccessSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('user_access')->insert([
            'user_id' => '6',
            'apikey' => 'ybWLba2jHq8cwJwiDPaTpAK4160G8t4m',
            'secretkey' => 'YLhDEX1srtWZ5IVuMwkwx9s2kNwR0tRY'
        ]);
    }
}
