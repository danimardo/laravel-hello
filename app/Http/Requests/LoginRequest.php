<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'username' => 'required_without:email|string|min:3|max:255',
            'email' => 'required_without:username|email|max:255',
            'password' => 'required|string|min:6',
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
            'username.required_without' => 'Debe proporcionar un nombre de usuario o email.',
            'email.required_without' => 'Debe proporcionar un email o nombre de usuario.',
            'username.min' => 'El nombre de usuario debe tener al menos 3 caracteres.',
            'email.email' => 'Debe proporcionar un email válido.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
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
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Trim whitespace from inputs if they exist
        if ($this->has('username')) {
            $this->merge(['username' => trim($this->input('username'))]);
        }
        if ($this->has('email')) {
            $this->merge(['email' => trim($this->input('email'))]);
        }
    }
}
