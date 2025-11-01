<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Determina si el usuario autenticado puede crear nuevos usuarios.
     * Solo permite la acción a Administradores.
     */
    public function create(User $user): Response
    {
        // Asume que el método isAdmin() está definido en tu modelo User
        return $user->isAdmin()
            ? Response::allow()
            : Response::deny('Solo los administradores pueden crear nuevos usuarios.');
    }

    /**
     * Determina si el usuario autenticado puede actualizar el modelo de usuario dado ($model).
     * * Reglas: 
     * 1. Debe ser Administrador.
     * 2. NO puede actualizarse a sí mismo (para evitar errores en el panel de administración).
     */
    public function update(User $user, User $model): Response
    {
        // 1. Debe ser administrador
        if (!$user->isAdmin()) {
            return Response::deny('Acción no autorizada. Requiere rol de administrador.');
        }

        // 2. No se puede auto-actualizar (excluyendo la cuenta actual)
        if ($user->id === $model->id) {
            return Response::deny('No puedes actualizar tu propia cuenta a través del panel de administración.');
        }

        return Response::allow();
    }

    /**
     * Determina si el usuario autenticado puede eliminar el modelo de usuario dado ($model).
     * * Reglas: 
     * 1. Debe ser Administrador.
     * 2. NO puede eliminarse a sí mismo.
     */
    public function delete(User $user, User $model): Response
    {
        // 1. Debe ser administrador
        if (!$user->isAdmin()) {
            return Response::deny('Acción no autorizada. Requiere rol de administrador.');
        }

        // 2. No se puede auto-eliminar
        if ($user->id === $model->id) {
            return Response::deny('No puedes eliminar tu propia cuenta a través del panel de administración.');
        }

        return Response::allow();
    }

    /**
     * Permite a cualquier usuario autenticado ver cualquier usuario. 
     * (Opcional, basado en el comportamiento actual de tu método show).
     */
    public function viewAny(User $user): Response
    {
        // Si quieres que cualquier usuario logueado pueda ver la lista de usuarios.
        return Response::allow();
    }
}
