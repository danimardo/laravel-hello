<?php

namespace App\Validators;

use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PasswordChangeValidator
{
    /**
     * Validate password change data.
     *
     * @param array $data
     * @return array Validated data
     * @throws ValidationException
     */
    public function validate(array $data): array
    {
        $validator = Validator::make($data, [
            'current_password' => 'required|string',
            'new_password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*()\-_=+{};:,<.>/?~])[a-zA-Z\d!@#$%^&*()\-_=+{};:,<.>/?~]{8,}$/'
            ],
            'new_password_confirmation' => 'required|string',
        ], [
            'current_password.required' => 'La contraseña actual es obligatoria',
            'new_password.required' => 'La nueva contraseña es obligatoria',
            'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres',
            'new_password.regex' => 'La nueva contraseña debe contener al menos: una minúscula, una mayúscula, un número y un carácter especial',
            'new_password.confirmed' => 'La confirmación de contraseña no coincide',
            'new_password_confirmation.required' => 'La confirmación de contraseña es obligatoria',
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return $validator->validated();
    }

    /**
     * Validate current password format.
     *
     * @param string $currentPassword
     * @return bool
     */
    public function validateCurrentPassword(string $currentPassword): bool
    {
        return strlen($currentPassword) > 0;
    }

    /**
     * Validate new password strength.
     *
     * @param string $password
     * @return array{isValid: bool, requirements: array}
     */
    public function validatePasswordStrength(string $password): array
    {
        $requirements = [
            'min_length' => strlen($password) >= 8,
            'has_lowercase' => preg_match('/[a-z]/', $password),
            'has_uppercase' => preg_match('/[A-Z]/', $password),
            'has_number' => preg_match('/\d/', $password),
            'has_special' => preg_match('/[!@#$%^&*()\-_=+{};:,<.>/?~]/', $password),
        ];

        $isValid = !in_array(false, $requirements);

        return [
            'isValid' => $isValid,
            'requirements' => $requirements,
        ];
    }

    /**
     * Check if password is different from current.
     *
     * @param string $currentPassword
     * @param string $newPassword
     * @return bool
     */
    public function isDifferent(string $currentPassword, string $newPassword): bool
    {
        return $currentPassword !== $newPassword;
    }

    /**
     * Get validation rules for password change.
     *
     * @return array
     */
    public function getRules(): array
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
     * Get custom validation messages.
     *
     * @return array
     */
    public function getMessages(): array
    {
        return [
            'current_password.required' => 'La contraseña actual es obligatoria',
            'new_password.required' => 'La nueva contraseña es obligatoria',
            'new_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres',
            'new_password.regex' => 'La nueva contraseña debe contener al menos: una minúscula, una mayúscula, un número y un carácter especial',
            'new_password.confirmed' => 'La confirmación de contraseña no coincide',
            'new_password_confirmation.required' => 'La confirmación de contraseña es obligatoria',
        ];
    }
}
