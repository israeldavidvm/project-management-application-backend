<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $newUser = new User;
        $newUser->name="Israel David Villarroel Moreno";
        $newUser->email="israeldavidvm@gmail.com";
        $newUser->password=bcrypt("Password1234.");
        $newUser->role=User::ROLE_ADMIN;
        $newUser->save();


        // 2. Crear Usuarios Desarrolladores
        User::create([
            'name' => 'Developer One',
            'email' => 'dev1@gmail.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_DEVELOPER,
        ]);


        // 2. Crear Usuarios Desarrolladores
        User::create([
            'name' => 'Developer two',
            'email' => 'dev2@gmail.com',
            'password' => bcrypt('password'),
            'role' => User::ROLE_DEVELOPER,
        ]);

    }
}
