<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use App\Models\Project;
use Illuminate\Database\Seeder;
use App\Http\Controllers\Api\V1\ProjectController;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $devs = User::where('role', User::ROLE_DEVELOPER)->get();
        $projects = Project::all();

        if ($devs->isEmpty() || $projects->isEmpty()) {
             return; 
        }

        $project1 = $projects->firstWhere('name', 'Plataforma de Gestión (CORE)');
        $project2 = $projects->firstWhere('name', 'Frontend Dashboard Vue.js');

        // Tareas para el Proyecto 1 (Asignadas a Developer One y Two)
        if ($project1) {
            // Tarea Completada (contribución: 40%)
            Task::create([
                'project_id' => $project1->id,
                'user_assignee_id' => $devs[0]->id, // Dev1
                'title' => 'Implementación de AuthController y Login',
                'description' => 'Configurar Sanctum y el flujo de autenticación.',
                'status' => Task::STATUS_COMPLETED,
            ]);

            // Tarea En Progreso (contribución: 30%)
            Task::create([
                'project_id' => $project1->id,
                'user_assignee_id' => $devs[1]->id, // Dev2
                'title' => 'CRUD del TaskController',
                'description' => 'Implementar las funciones CRUD de tareas con validación y policies.',
                'status' => Task::STATUS_IN_PROGRESS,
            ]);
            
            // Tarea Pendiente (contribución: 30%)
            Task::create([
                'project_id' => $project1->id,
                'user_assignee_id' => $devs[0]->id, // Dev1
                'title' => 'Lógica de Cálculo de Progreso del Proyecto',
                'description' => 'Integrar el cálculo de progreso en los controladores.',
                'status' => Task::STATUS_PENDING,
            ]);
        }

        // Tareas para el Proyecto 2 (Asignadas a Developer Two)
        if ($project2) {
            Task::create([
                'project_id' => $project2->id,
                'user_assignee_id' => $devs[1]->id, // Dev2
                'title' => 'Diseño e implementación del Dashboard Dev',
                'description' => 'Mostrar proyectos creados/asignados y conteo de tareas por estado.',
                'status' => Task::STATUS_PENDING,
            ]);
        }
        
        // Después de crear todas las tareas, actualizamos el progreso de los proyectos
        $projects->each(function ($project) {
            ProjectController::calculateProgress($project);
        });
    }
}