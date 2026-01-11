<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChangePasswordRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>/?~])[a-zA-Z\d!@#$%^&*()\-_=+{};:,<.>/?~]{8,}$/'
            ],
            'new_password_confirmation' => 'required|string',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'current_password.required' => 'La contraseña actual es obligatoria',
            'new_password.required' => 'La nueva contraseña es obligatoria',
            'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres',
            'new_password.regex' => 'La nueva contraseña debe contener al menos: una minúscula, una mayúscula, un número y un carácter especial (!@#$%^&*()-_=+{};:,<.>/?~)',
            'new_password.confirmed' => 'La confirmación de contraseña no coincide',
            'new_password_confirmation.required' => 'La confirmación de contraseña es obligatoria',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'current_password' => 'contraseña actual',
            'new_password' => 'nueva contraseña',
            'new_password_confirmation' => 'confirmación de contraseña',
        ];
    }
}
