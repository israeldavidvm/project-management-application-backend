<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Project;
use App\Models\User;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $admin = User::where('role', User::ROLE_ADMIN)->first();
        $developers = User::where('role', User::ROLE_DEVELOPER)->get();

        if ($admin) {
            // Proyecto 1: Creado por el Admin
            Project::create([
                'name' => 'Plataforma de Gestión (CORE)',
                'description' => 'Desarrollo de la API REST y el Dashboard Admin/Dev.',
                'user_creator_id' => $admin->id,
            ]);
        }

        // Proyectos 2 y 3: Creados por el Developer One
        if (isset($developers[0])) {
            Project::create([
                'name' => 'Frontend Dashboard Vue.js',
                'description' => 'Implementación de las vistas del Dashboard y lógica de estado (Pinia).',
                'user_creator_id' => $developers[0]->id,
            ]);
            
            Project::create([
                'name' => 'Módulo de Reportes',
                'description' => 'Desarrollo de las interfaces y API para reportes trimestrales.',
                'user_creator_id' => $developers[1]->id,
            ]);
        }
    }
}
