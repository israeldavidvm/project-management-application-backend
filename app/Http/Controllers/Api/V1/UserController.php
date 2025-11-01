<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\V1\UserRequest;
use Illuminate\Support\Facades\Response;

class UserController extends Controller 
{

    /**
     * Muestra una lista de usuarios.
     * Si el usuario autenticado es Admin, lista todos. 
     * Si es Developer, lista solo otros desarrolladores.
     * (Usado para selectores en asignación de tareas).
     */
    #[OA\Get(
        path: '/api/v1/users',
        summary: 'Listar usuarios (Filtro por rol)',
        description: 'Si el usuario autenticado es Administrador, retorna todos los usuarios. Si es Desarrollador, retorna solo otros desarrolladores.',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200, 
                description: 'Lista de usuarios obtenida exitosamente',
                content: new OA\JsonContent(
                    type: 'array', 
                    items: new OA\Items(ref: '#/components/schemas/UserResource')
                )
            ),
            new OA\Response(response: 401, description: 'No autorizado'),
            new OA\Response(response: 403, description: 'Prohibido (Acceso denegado a ciertos roles)')
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $query = User::query();

        // Si el usuario NO es un administrador, solo ve a los desarrolladores.
        // Asumimos que isAdmin() existe en el modelo User
        if (!$user->isAdmin()) {
            // Asumo que tu modelo User tiene la constante ROLE_DEVELOPER
            $query->where('role', User::ROLE_DEVELOPER);
        }

        $users = $query->select('id', 'name', 'email', 'role')
                       ->orderBy('name')
                       ->get();

        // Usamos UserResource::collection para estandarizar el formato de salida
        return Response::json(UserResource::collection($users));
    }
    
    /**
     * Obtiene una lista de todos los usuarios con el rol 'desarrollador'.
     * Útil para selectores de asignación de tareas.
     */
    #[OA\Get(
        path: '/api/v1/users/developers',
        summary: 'Obtener solo usuarios con rol de Desarrollador',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200, 
                description: 'Lista de desarrolladores obtenida exitosamente',
                content: new OA\JsonContent(
                    type: 'array', 
                    items: new OA\Items(ref: '#/components/schemas/UserResource')
                )
            ),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function getDevelopers(): JsonResponse
    {
        // Asumo que tu modelo User tiene la constante ROLE_DEVELOPER
        $developers = User::where('role', User::ROLE_DEVELOPER)
                          ->select('id', 'name', 'email', 'role')
                          ->orderBy('name')
                          ->get();

        return Response::json(UserResource::collection($developers));
    }

    /**
     * Obtiene una lista de todos los usuarios con el rol 'administrador'.
     * Útil para selectores de escalamiento o contacto.
     */
    #[OA\Get(
        path: '/api/v1/users/admins',
        summary: 'Obtener solo usuarios con rol de Administrador',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200, 
                description: 'Lista de administradores obtenida exitosamente',
                content: new OA\JsonContent(
                    type: 'array', 
                    items: new OA\Items(ref: '#/components/schemas/UserResource')
                )
            ),
            new OA\Response(response: 401, description: 'No autorizado')
        ]
    )]
    public function getAdmins(): JsonResponse
    {
        // Asumo que tu modelo User tiene la constante ROLE_ADMIN
        $admins = User::where('role', User::ROLE_ADMIN)
                      ->select('id', 'name', 'email', 'role')
                      ->orderBy('name')
                      ->get();

        return Response::json(UserResource::collection($admins));
    }


    // ----------------------------------------------------------------------
    // FUNCIONES CRUD (USADAS SOLO POR EL ADMINISTRADOR EN EL PANEL)
    // ----------------------------------------------------------------------

    /**
     * Crea un nuevo usuario. (Solo Admin, gestionado por Policy)
     */
    #[OA\Post(
        path: '/api/v1/users', // Usando la convención RESTful: POST a /users
        summary: 'Crear un nuevo usuario (Solo Admin)',
        tags: ['Users (Admin CRUD)'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody( // <-- MODIFICADO: Esquema Request en línea
            required: true,
            description: 'Datos requeridos para la creación del usuario',
            content: new OA\JsonContent(
                required: ['name', 'email', 'password', 'role'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Juan Pérez'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan.perez@dominio.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'MiContraseñaSegura123'),
                    new OA\Property(property: 'role', type: 'string', enum: ['administrator', 'developer'], example: 'developer'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 201, 
                description: 'Usuario creado exitosamente',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResource')
            ),
            new OA\Response(response: 401, description: 'No autorizado'),
            new OA\Response(response: 403, description: 'Prohibido (Solo administradores)'),
            new OA\Response(response: 422, description: 'Error de validación')
        ]
    )]
    public function store(UserRequest $request): JsonResponse
    {
        // AUTORIZACIÓN: Verifica si el usuario autenticado tiene permiso para crear un User.
        Gate::authorize('create', User::class);

        $data = $request->validated();
        
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']), 
            'role' => $data['role'], 
        ]);

        return response()->json(new UserResource($user), 201);
    }

    /**
     * Muestra un usuario específico. (Puede usarse por el propio usuario o admin)
     */
    #[OA\Get(
        path: '/api/v1/users/{user}',
        summary: 'Mostrar un usuario específico',
        tags: ['Users'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'ID del usuario a mostrar',
                schema: new OA\Schema(type: 'integer', format: 'int64')
            ),
        ],
        responses: [
            new OA\Response(
                response: 200, 
                description: 'Usuario encontrado',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResource')
            ),
            new OA\Response(response: 401, description: 'No autorizado'),
            new OA\Response(response: 404, description: 'Usuario no encontrado')
        ]
    )]
    public function show(User $user): JsonResponse
    {
        // Si no hay Policy de vista, cualquiera autenticado puede ver.
        return response()->json(new UserResource($user), 200);
    }

    /**
     * Actualiza un usuario específico. (Solo Admin, excluye a sí mismo, gestionado por Policy)
     */
    #[OA\Put(
        path: '/api/v1/users/{user}',
        summary: 'Actualizar un usuario (Solo Admin)',
        tags: ['Users (Admin CRUD)'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'ID del usuario a actualizar',
                schema: new OA\Schema(type: 'integer', format: 'int64')
            ),
        ],
        requestBody: new OA\RequestBody( // <-- MODIFICADO: Esquema Request en línea
            required: true,
            description: 'Datos a actualizar para el usuario. La contraseña es opcional.',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Juan Pablo'),
                    new OA\Property(property: 'email', type: 'string', format: 'email', example: 'juan.pablo@dominio.com'),
                    new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'NuevaContraseña123', nullable: true),
                    new OA\Property(property: 'role', type: 'string', enum: ['administrator', 'developer'], example: 'administrator'),
                ],
                type: 'object'
            )
        ),
        responses: [
            new OA\Response(
                response: 200, 
                description: 'Usuario actualizado exitosamente',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResource')
            ),
            new OA\Response(response: 401, description: 'No autorizado'),
            new OA\Response(response: 403, description: 'Prohibido (Admin no puede auto-actualizarse el rol)'),
            new OA\Response(response: 422, description: 'Error de validación')
        ]
    )]
    public function update(UserRequest $request, User $user): JsonResponse
    {
        // AUTORIZACIÓN: Verifica si el usuario autenticado tiene permiso para actualizar $user.
        // La Policy comprueba si es Admin Y si no se está auto-actualizando.
        Gate::authorize('update', $user);
        
        $data = $request->validated();
        
        // Manejo condicional de la contraseña (solo si se proporciona)
        if (isset($data['password'])) {
            $user->password = bcrypt($data['password']);
            unset($data['password']); 
        }

        // El resto de campos (name, email, role) se actualizan
        $user->update($data);

        return response()->json(new UserResource($user), 200);
    }

    /**
     * Elimina un usuario específico. (Solo Admin, excluye a sí mismo, gestionado por Policy)
     */
    #[OA\Delete(
        path: '/api/v1/users/{user}',
        summary: 'Eliminar un usuario (Solo Admin)',
        tags: ['Users (Admin CRUD)'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(
                name: 'user',
                in: 'path',
                required: true,
                description: 'ID del usuario a eliminar',
                schema: new OA\Schema(type: 'integer', format: 'int64')
            ),
        ],
        responses: [
            new OA\Response(
                response: 204, 
                description: 'Usuario eliminado exitosamente (Sin contenido)'
            ),
            new OA\Response(response: 401, description: 'No autorizado'),
            new OA\Response(response: 403, description: 'Prohibido (Admin no puede auto-eliminarse)'),
            new OA\Response(response: 404, description: 'Usuario no encontrado')
        ]
    )]
    public function destroy(User $user): JsonResponse
    {
        // AUTORIZACIÓN: Verifica si el usuario autenticado tiene permiso para eliminar $user.
        // La Policy comprueba si es Admin Y si no se está auto-eliminando.
        Gate::authorize('delete', $user);
        
        // La comprobación de auto-eliminación se movió a la Policy.
        $user->delete();

        return response()->json(null, 204);
    }
}
