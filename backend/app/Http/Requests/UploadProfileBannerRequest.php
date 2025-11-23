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
            'profile_banner' => 'image|mimes:jpg,jpeg,png',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'profile_banner.image' => 'Profile banner must be an image',
            'profile_banner.mimes' => 'Profile banner must be JPG, JPEG, or PNG format',
            // max removed to allow unrestricted uploads
        ];
    }
}
