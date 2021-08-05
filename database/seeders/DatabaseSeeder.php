<?php

namespace Database\Seeders;

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // $this->call('UsersTableSeeder');
        DB::table('users')->insert([
            'name' => 'hamid',
            'email' => 'kalagemet@icloud.com',
            'password' => app('hash')->make('siangmalam'),
        ]);
        DB::table('profile')->insert([
            'banner' => '/img/banner/banner.jpg',
            'logo' => '/img/logo/desalase.id.png',
            'token' => 123
        ]);
    }
}
