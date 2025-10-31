<?php

namespace App\Swagger;

use OpenApi\Attributes as OA;


#[OA\Components(
    schemas:[

    ],
    securitySchemes:[
        new OA\SecurityScheme(
            securityScheme: 'sanctum',
            type: 'http',
            scheme: 'bearer',
            description: 'Enter token in format (Bearer <token>) (Ejemplo: Bearer 2|Cfz3yDjKqUh55AUI6I9nQQv6MEHsEqQvJToMDnJ7e7c8478a)',
            in: 'header',
            name: 'Authorization',
        ),
    ]

)]
class ShareBase
{
    // Esta clase sigue siendo un "gancho" para que Swagger-PHP
    // pueda procesar los atributos de nivel de documento asociados a ella.
}