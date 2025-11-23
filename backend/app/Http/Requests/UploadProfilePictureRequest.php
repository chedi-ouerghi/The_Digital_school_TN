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
                'file',
                'image',
                'mimes:jpeg,png,gif,webp,jpg',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'profile_picture.file' => 'Le fichier doit être valide.',
            'profile_picture.image' => 'Le fichier doit être une image valide.',
            'profile_picture.mimes' => 'Les formats acceptés sont: jpeg, png, gif, webp.',
        ];
    }
}
