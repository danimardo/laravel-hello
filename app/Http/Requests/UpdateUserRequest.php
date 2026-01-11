<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled by middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $userId = $this->route('user') ?? $this->user;

        return [
            'username' => [
                'sometimes',
                'required',
                'string',
                'min:3',
                'max:255',
                Rule::unique('users', 'username')->ignore($userId),
            ],
            'email' => [
                'sometimes',
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($userId),
            ],
            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>/?~])[a-zA-Z\d!@#$%^&*()\-_=+{};:,<.>/?~]{8,}$/'
            ],
            'password_confirmation' => 'nullable|string|same:password',
            'role' => 'sometimes|required|in:admin,user',
            'status' => 'sometimes|required|in:active,inactive',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'username.required' => 'El nombre de usuario es obligatorio.',
            'username.min' => 'El nombre de usuario debe tener al menos 3 caracteres.',
            'username.unique' => 'El nombre de usuario ya está en uso.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'Debe proporcionar un email válido.',
            'email.unique' => 'El email ya está en uso.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.regex' => 'La contraseña debe contener al menos: una minúscula, una mayúscula, un número y un carácter especial.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'role.required' => 'El rol es obligatorio.',
            'role.in' => 'El rol debe ser admin o user.',
            'status.required' => 'El estado es obligatorio.',
            'status.in' => 'El estado debe ser active o inactive.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'username' => 'nombre de usuario',
            'email' => 'email',
            'password' => 'contraseña',
            'password_confirmation' => 'confirmación de contraseña',
            'role' => 'rol',
            'status' => 'estado',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from inputs
        $this->merge([
            'username' => $this->username ? trim($this->username) : null,
            'email' => $this->email ? trim($this->email) : null,
        ]);
    }
}
