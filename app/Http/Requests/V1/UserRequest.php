<?php

namespace App\Http\Requests\V1;

use App\Models\User;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Solo los administradores deberían poder hacer estas peticiones.
     */
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->role === 'administrador';
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // Determina si es una petición de creación (POST) o actualización (PUT/PATCH)
        $isStore = $this->isMethod('post');

        // Reglas de complejidad unificadas (sin 'required' ni 'nullable')
        $complexPasswordRules = [
            'string',
            'max:255',
            'min:8',
            'confirmed', // Requiere 'password_confirmation'
            // Expresión Regular: Al menos 8 caracteres, 1 mayúscula, 1 minúscula, 1 dígito, 1 símbolo (.$%@!&*+)
            'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[.$%@!&*+]).*$/',
        ];

        // Determina si la contraseña es requerida o no (obligatoria en creación, opcional en actualización)
        $passwordBaseRule = $isStore ? 'required' : 'nullable';

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            // unique solo para creación, o ignorando el ID actual para actualización
            'email' => [
                'required', 
                'string', 
                'email', 
                'max:255', 
                Rule::unique('users')->ignore($this->user) // El 'user' es el parámetro de ruta
            ],
            // Aplicamos la regla base y las reglas de complejidad
            'password' => array_merge([$passwordBaseRule], $complexPasswordRules),
            
            // Regla de seguridad para el rol
            'role' => [
                'required', 
                // Asumo que tienes una constante en User::class para los roles
                Rule::in([User::ROLE_ADMIN, User::ROLE_DEVELOPER]) 
            ],
        ];

        // Regla de seguridad: Si estamos actualizando, no permitir que un administrador
        // se cambie su propio rol a algo diferente o se des-administre a sí mismo.
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $targetUser = $this->route('user');
            
            if ($targetUser && Auth::user()->id === $targetUser->id) {
                // Si el usuario objetivo es el usuario autenticado:
                $rules['role'] = [
                    'required', 
                    // El rol NO puede cambiarse si es un administrador editándose a sí mismo
                    Rule::in([User::ROLE_ADMIN]) 
                ];
                // La contraseña debe seguir siendo 'nullable'
                $rules['password'] = array_merge(['nullable'], $complexPasswordRules);
            }
        }

        return $rules;
    }

    /**
     * Personaliza los mensajes de validación
     */
    public function messages(): array
    {
        return [
            'role.in' => 'El rol seleccionado no es válido. Debe ser "administrador" o "desarrollador".',
            'password.required' => 'La contraseña es obligatoria para la creación de un nuevo usuario.',
            'password.confirmed' => 'La confirmación de la contraseña no coincide.',
            'password.regex' => 'La contraseña debe contener al menos 8 caracteres e incluir al menos una mayúscula, una minúscula, un número y uno de los siguientes símbolos: . $ % @ ! & * +.',
        ];
    }
}
