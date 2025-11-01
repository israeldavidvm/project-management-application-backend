<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /**
     * Constantes para los posibles estados de una tarea.
     */
    const STATUS_PENDING    = 'pendiente';
    const STATUS_IN_PROGRESS = 'en progreso';
    const STATUS_COMPLETED  = 'completada';

    /**
     * Los atributos que son asignables masivamente.
     * Incluimos 'status' ya que se actualizan a menudo.
     */
    protected $fillable = [
        'project_id',
        'user_assignee_id',
        'title',
        'description',
        'status',
    ];

    /**
     * El estado por defecto para nuevas tareas.
     */
    protected $attributes = [
        'status' => self::STATUS_PENDING,
    ];

    // --- RELACIONES ---

    /**
     * Una tarea pertenece a un proyecto.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Una tarea puede estar asignada a un usuario específico.
     * Utilizamos la clave foránea user_assignee_id.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function userAssignee()
    {
        return $this->belongsTo(User::class, 'user_assignee_id');
    }
}