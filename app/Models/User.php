<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use OpenApi\Attributes as OA;

/**
 * Class User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $role
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[OA\Schema(
    schema: 'User',
    title: 'Modelo de Usuario',
    description: 'Representación del objeto Usuario en la base de datos, incluyendo su rol.',
    required: ['id', 'name', 'email', 'role'],
    properties: [
        new OA\Property(property: 'id', type: 'integer', readOnly: true, description: 'ID único del usuario.'),
        new OA\Property(property: 'name', type: 'string', description: 'Nombre completo del usuario.'),
        new OA\Property(property: 'email', type: 'string', format: 'email', description: 'Correo electrónico único.'),
        new OA\Property(property: 'role', type: 'string', description: 'Rol del usuario.', enum: ['administrador', 'desarrollador']),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', description: 'Fecha de creación del registro.'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', description: 'Fecha de la última actualización del registro.'),
    ]
)]
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    const ROLE_ADMIN = 'administrador';
    const ROLE_DEVELOPER = 'desarrollador';

    protected $table = 'users';

    protected $casts = [
        'email_verified_at' => 'datetime'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    protected $fillable = [
        'name',
        'email',
        'email_verified_at',
        'password',
        'remember_token',
        'role'
    ];

    // --- RELACIONES DE PROYECTOS ---

    /**
     * Proyectos creados por este usuario.
     */
    public function projectsCreated()
    {
        return $this->hasMany(Project::class, 'user_creator_id');
    }

    /**
     * Proyectos en los que el usuario tiene tareas asignadas.
     */
    public function projectsAssigned()
    {
        return $this->hasManyThrough(
            Project::class,
            Task::class,
            'user_assignee_id',
            'id',
            'id',
            'project_id'
        )->distinct();
    }

    // --- RELACIONES DE TAREAS ---
    
    /**
     * Tareas asignadas a este usuario.
     */
    public function tasksAssigned() 
    {
        return $this->hasMany(Task::class, 'user_assignee_id');
    }

    // --- MÉTODOS DE ROL ---

    /**
     * Verifica si el usuario es administrador.
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Verifica si el usuario es desarrollador.
     */
    public function isDeveloper(): bool
    {
        return $this->role === self::ROLE_DEVELOPER;
    }
}
