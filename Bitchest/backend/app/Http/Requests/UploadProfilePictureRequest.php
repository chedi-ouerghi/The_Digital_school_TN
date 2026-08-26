<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadProfilePictureRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'profile_picture' => [
                'nullable',  // Fichier facultatif traité par le contrôleur
                'file',
                'image',
                'mimes:jpeg,png,gif,webp,jpg',
                'max:5120',  // Taille maximale de 5 Mo
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'profile_picture.required' => 'Une image est requise.',
            'profile_picture.file' => 'Le fichier doit être valide.',
            'profile_picture.image' => 'Le fichier doit être une image valide.',
            'profile_picture.mimes' => 'Les formats acceptés sont: jpeg, png, gif, webp.',
            'profile_picture.max' => 'La taille maximale est 5MB.',
        ];
    }
}
