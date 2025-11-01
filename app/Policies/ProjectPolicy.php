<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Project;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    /**
     * Determina si el usuario puede crear una nueva Task en el Projecto.
     * Esto se mapea a la habilidad 'createTask' que usarías en el controlador.
     */
    public function createTask(User $user, Project $project): bool
    {
        // 1. Un administrador siempre puede crear tareas en cualquier proyecto.
        if ($user->isAdmin()) {
            return true;
        }

        // 2. Solo el usuario que creó el proyecto (creator) puede añadir tareas.
        // Asumiendo que el modelo Project tiene una columna user_creator_id
        return $user->id === $project->user_creator_id;
    }
}
