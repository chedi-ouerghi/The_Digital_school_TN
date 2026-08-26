<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddCryptoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        // ADMIN role validation is handled in the controller
        return auth()->check();
    }

    /**
     * Get the validation rules
     */
    public function rules(): array
    {
        return [
            // Champs obligatoires de la cryptomonnaie
            'symbol' => [
                'required',
                'string',
                'max:20',
                'uppercase',
                'unique:cryptomoney,symbol,' . ($this->crypto->id ?? 'NULL') . ',id',
                'regex:/^[A-Z0-9\-]+$/',
            ],
            'name' => [
                'required',
                'string',
                'max:255',
                'unique:cryptomoney,name,' . ($this->crypto->id ?? 'NULL') . ',id',
            ],

            // Champs complémentaires facultatifs
            'coingecko_id' => [
                'nullable',
                'string',
                'max:100',
                'lowercase',
            ],
            'category' => [
                'nullable',
                'string',
                'max:100',
            ],
            'website' => [
                'nullable',
                'url',
                'max:255',
            ],
            'market_cap' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'volume_24h' => [
                'nullable',
                'numeric',
                'min:0',
            ],
            'change_24h_pct' => [
                'nullable',
                'numeric',
                'between:-100,100',
            ],
            'image' => [
                'nullable',
                'file',
                'image',
                'mimes:jpeg,png,gif,webp',
                'max:2048',
            ],
        ];
    }

    /**
     * Custom validation error messages
     */
    public function messages(): array
    {
        return [
            'symbol.required' => 'The symbol is required.',
            'symbol.uppercase' => 'The symbol must be uppercase.',
            'symbol.unique' => 'This symbol already exists.',
            'symbol.regex' => 'The symbol may only contain uppercase letters, numbers, and hyphens.',

            'name.required' => 'The name is required.',
            'name.unique' => 'This name already exists.',

            'coingecko_id.lowercase' => 'The CoinGecko ID must be lowercase.',
            'website.url' => 'The website must be a valid URL.',

            'market_cap.numeric' => 'Market cap must be a number.',
            'market_cap.min' => 'Market cap cannot be negative.',

            'volume_24h.numeric' => '24h volume must be a number.',
            'volume_24h.min' => '24h volume cannot be negative.',

            'change_24h_pct.numeric' => '24h change must be a number.',
            'change_24h_pct.between' => '24h change must be between -100% and +100%.',

            'image.image' => 'The file must be a valid image.',
            'image.mimes' => 'Allowed image formats: jpeg, png, gif, webp.',
            'image.max' => 'The maximum image size is 2MB.',
        ];
    }

    /**
     * Prepare the data for validation
     */
    protected function prepareForValidation(): void
    {
        // Normalisation du symbole en majuscules
        if ($this->has('symbol')) {
            $this->merge([
                'symbol' => strtoupper($this->input('symbol')),
            ]);
        }

        // Normalisation de l'identifiant CoinGecko en minuscules
        if ($this->has('coingecko_id')) {
            $this->merge([
                'coingecko_id' => strtolower($this->input('coingecko_id')),
            ]);
        }

        // Normalisation du format des URLs de site web
        if ($this->has('website')) {
            $website = $this->input('website');
            if (
                !str_starts_with($website, 'http://') &&
                !str_starts_with($website, 'https://')
            ) {
                $this->merge([
                    'website' => 'https://' . $website,
                ]);
            }
        }

        // Conversion des valeurs numériques
        if ($this->has('market_cap')) {
            $this->merge([
                'market_cap' => (float) $this->input('market_cap'),
            ]);
        }

        if ($this->has('volume_24h')) {
            $this->merge([
                'volume_24h' => (float) $this->input('volume_24h'),
            ]);
        }

        if ($this->has('change_24h_pct')) {
            $this->merge([
                'change_24h_pct' => (float) $this->input('change_24h_pct'),
            ]);
        }
    }
}
