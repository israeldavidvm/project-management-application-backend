<?php

namespace App\Swagger\V1;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
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

class Base {}