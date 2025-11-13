<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCryptoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'crypto_id' => ['required', 'string'],
            'image' => ['nullable', 'file', 'image', 'mimes:jpeg,png,gif,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'image.image' => 'Le fichier doit être une image valide',
            'image.mimes' => 'Les formats acceptés sont: jpeg, png, gif, webp',
            'image.max' => 'La taille maximale est 2MB',
        ];
    }
}
