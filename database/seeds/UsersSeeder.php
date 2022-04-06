<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('users')->insert([
            'id' => '6',
            'name' => 'testUser',
            'email' => 'v.ignatov@p5s-team.ru',
            'password' => \Illuminate\Support\Str::random(32)
        ]);
    }
}
