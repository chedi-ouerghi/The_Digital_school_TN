<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadProfileBannerRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'profile_banner' => [
                'nullable',  // Allow null (we'll check in controller)
                'file',
                'image',
                'mimes:jpg,jpeg,png,gif,webp',
                'max:10240',  // Max 10MB for banner
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'profile_banner.required' => 'La bannière est requise.',
            'profile_banner.file' => 'Le fichier doit être valide.',
            'profile_banner.image' => 'La bannière doit être une image valide.',
            'profile_banner.mimes' => 'La bannière doit être au format JPG, JPEG, PNG ou WebP.',
            'profile_banner.max' => 'La taille maximale est 10MB.',
        ];
    }
}
