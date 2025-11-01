<?php

namespace App\Swagger\V1;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '2.0.0',
    title: 'API del sitio',
    description: 'API para el manejo de datos del sitio',
    contact: new OA\Contact(
        email: 'israeldavidvm@gmail.com',
        name: 'Israel David Villaroel Moreno'
    ),
    license: new OA\License(
        name: 'Licencia abierta',
        url: 'https://opensource.org/licenses/MIT'
    )
)]


#[OA\Schema(
            schema: 'User',
            type: 'object',
            description: 'User Schema',
            properties: [
                new OA\Property(property: 'id', type: 'integer', example: 1),
                new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                new OA\Property(property: 'email', type: 'string', example: 'user@example.com'),
                 new OA\Property(
                    property: 'email_verified_at',
                    type: 'string',
                    format: 'date-time',
                    description: 'The email verification timestamp'
                ),
                new OA\Property(
                    property: 'created_at',
                    type: 'string',
                    format: 'date-time',
                    description: 'Creation date of the user' // Descripción actualizada
                ),
                new OA\Property(
                    property: 'updated_at',
                    type: 'string',
                    format: 'date-time',
                    description: "Last Update date of the user"
                )
            ]
        ),
]
class Base {}