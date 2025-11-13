<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminCreateClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // autorisé au niveau route via middleware role:ADMIN
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            // role should be CLIENT for created user
            'role' => ['nullable', 'in:CLIENT,ADMIN'],
            'balance_eur' => ['nullable', 'numeric'],
        ];
    }
}
