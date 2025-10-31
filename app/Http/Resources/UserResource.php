<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: "UserResource",
    title: "Salida de Usuario (Resource)",
    description: "Estructura de datos para representar un usuario en la respuesta de la API.",
    properties: [
        new OA\Property(property: "id", type: "integer", example: 1, description: "ID único del usuario."),
        new OA\Property(property: "name", type: "string", example: "Alice Smith", description: "Nombre completo."),
        new OA\Property(property: "email", type: "string", format: "email", example: "alice.smith@example.com", description: "Correo electrónico."),
        new OA\Property(property: "role", type: "string", example: "administrador", description: "Rol del usuario."),
        new OA\Property(property: "email_verified_at", type: "string", format: "date-time", nullable: true, example: "2023-10-27T10:00:00.000000Z", description: "Marca de tiempo de verificación de email."),
        new OA\Property(property: "created_at", type: "string", format: "date-time", example: "2023-10-27T10:00:00.000000Z", description: "Fecha de creación."),
        new OA\Property(property: "updated_at", type: "string", format: "date-time", example: "2023-10-27T11:30:00.000000Z", description: "Fecha de última modificación."),
    ]
)]
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'role' => $this->role,
            'email_verified_at'=> $this->email_verified_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
