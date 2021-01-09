<?php

use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Admin::Create([
            'username' => 'admin',
            'email' => 'admin@gmail.com',
            'status' => 1,
            'password' => bcrypt('123456'),
        ]);
    }
}
