<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Task; 

class Project extends Model
{
    /**
     * The attributes that are mass assignable.
     * Estos campos pueden ser llenados usando el método create() o fill().
     */
    protected $fillable = [
        'user_creator_id',
        'name',
        'description',
        'progress_percentage',
    ];

    // Un proyecto pertenece a un usuario (creador)
    public function userCreator()
    {
        return $this->belongsTo(User::class, 'user_creator_id');
    }

    // Un proyecto tiene múltiples tareas
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
    
    /**
     * Lógica para determinar si un usuario pertenece a este proyecto (es decir, tiene tareas asignadas).
     * @param User $user
     * @return bool
     */
    public function isMember(User $user): bool
    {
        // Determina si existe al menos una tarea en este proyecto asignada al usuario dado.
        return $this->tasks()
                    ->where('user_assignee_id', $user->id)
                    ->exists();
    }
}
