<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class LoginValidator
{
    /**
     * Validate login credentials.
     *
     * @param array $data
     * @return array Validated data
     * @throws ValidationException
     */
    public function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'username' => 'required_without:email|string',
            'email' => 'required_without:username|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return $validator->validated();
    }

    /**
     * Validate that either username or email is provided.
     *
     * @param array $data
     * @return bool
     */
    public function hasIdentifier(array $data): bool
    {
        return !empty($data['username']) || !empty($data['email']);
    }

    /**
     * Get the login identifier (username or email).
     *
     * @param array $data
     * @return string|null
     */
    public function getIdentifier(array $data): ?string
    {
        return $data['username'] ?? $data['email'] ?? null;
    }

    /**
     * Validate that password meets minimum requirements.
     *
     * @param string $password
     * @return bool
     */
    public function validatePassword(string $password): bool
    {
        return strlen($password) > 0;
    }

    /**
     * Get validation rules for login.
     *
     * @return array
     */
    public function getRules(): array
    {
        return [
            'username' => 'required_without:email|string',
            'email' => 'required_without:username|email',
            'password' => 'required|string',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function getMessages(): array
    {
        return [
            'username.required_without' => 'Debe proporcionar un nombre de usuario o email',
            'email.required_without' => 'Debe proporcionar un email o nombre de usuario',
            'email.email' => 'Debe proporcionar un email válido',
            'password.required' => 'La contraseña es obligatoria',
        ];
    }
}
