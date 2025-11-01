<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProjectController extends Controller
{
    /**
     * Muestra una lista de proyectos.
     * Admin: Muestra todos los proyectos.
     * Developer: Muestra proyectos creados por él o en los que tiene tareas asignadas.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role === User::ROLE_ADMIN) {
            // Un administrador ve todos los proyectos.
            $projects = Project::with('userCreator', 'tasks')->get();
        } else {
            // Un desarrollador ve proyectos creados por él o en los que participa.
            
            // 1. Proyectos donde es el creador
            $createdProjects = $user->projectsCreated()->with('userCreator', 'tasks')->get();
            
            // 2. Proyectos donde tiene tareas asignadas (usa la relación hasManyThrough)
            $participatingProjects = $user->projectsAssigned()->with('userCreator', 'tasks')->get();
            
            // Combina los resultados y asegura que no haya duplicados.
            $projects = $createdProjects->merge($participatingProjects)->unique('id')->values();
        }

        return response()->json($projects);
    }

    /**
     * Almacena un proyecto recién creado. (Validación Directa)
     */
    public function store(Request $request)
    {
        // Validar datos de entrada
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $projectData = $validator->validated();
        
        // Asocia el proyecto al usuario autenticado usando la clave user_creator_id.
        $projectData['user_creator_id'] = auth()->id();
        
        $project = Project::create($projectData);

        return response()->json([
            'message' => 'Project created successfully',
            'project' => $project->load('userCreator'),
        ], 201);
    }

    /**
     * Muestra un proyecto específico.
     */
    public function show(Project $project)
    {
        // Autorización: Permite el acceso a Admin, al Creador del proyecto, o a un usuario que sea miembro (tenga tareas asignadas).
        if (auth()->user()->role !== User::ROLE_ADMIN && 
            auth()->id() !== $project->user_creator_id && 
            !$project->isMember(auth()->user())) 
        {
            return response()->json(['message' => 'Unauthorized to view this project.'], 403);
        }

        return response()->json($project->load(['userCreator', 'tasks.userAssignee']));
    }

    /**
     * Actualiza un proyecto específico. (Validación Directa)
     */
    public function update(Request $request, Project $project)
    {
        // La Policy debe asegurar que solo el Creador o un Admin puede actualizar.
        Gate::authorize('update', $project);
        
        // Validar datos de entrada
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            // El campo progress_percentage se gestiona internamente, no debe ser actualizable por el usuario.
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $project->update($validator->validated());

        return response()->json([
            'message' => 'Project updated successfully',
            'project' => $project->load('userCreator'),
        ]);
    }

    /**
     * Elimina un proyecto específico.
     */
    public function destroy(Project $project)
    {
        // La Policy debe asegurar que solo el Creador o un Admin puede eliminar.
        Gate::authorize('delete', $project);

        // Se asume que las tareas asociadas se eliminan en cascada gracias a la migración.
        $project->delete();

        return response()->json(['message' => 'Project deleted successfully'], 204);
    }

    /**
     * Calcula y actualiza el porcentaje de progreso del proyecto.
     * Ahora basado en (Tareas Completadas / Tareas Totales) * 100.
     * Este método estático debe ser llamado desde TaskController cuando el estado de una tarea cambia.
     */
    public static function calculateProgress(Project $project): float
    {
        // Forzar la recarga de las tareas para asegurar que los datos estén frescos antes del cálculo
        $project->load('tasks');

        $totalTasks = $project->tasks->count();

        if ($totalTasks === 0) {
            $project->progress_percentage = 0.0;
        } else {
            // Contar el número de tareas que tienen el estado 'completada'
            $completedTasks = $project->tasks->where('status', Task::STATUS_COMPLETED)->count();

            // Calcular el porcentaje basado en el CONTEO de tareas (no en la contribución individual)
            $progress = ($completedTasks / $totalTasks) * 100;
            
            // Redondear al entero más cercano
            $project->progress_percentage = round($progress, 2);
        }
        
        $project->save();

        return $project->progress_percentage;
    }
}
