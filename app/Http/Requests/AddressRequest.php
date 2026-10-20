<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool { return $this->user() !== null; }

    public static function ruleSet(string $prefix = ''): array
    {
        return [
            $prefix.'full_name' => ['required', 'string', 'max:120'],
            $prefix.'phone' => ['nullable', 'string', 'max:40'],
            $prefix.'line1' => ['required', 'string', 'max:190'],
            $prefix.'line2' => ['nullable', 'string', 'max:190'],
            $prefix.'city' => ['required', 'string', 'max:100'],
            $prefix.'state' => ['nullable', 'string', 'max:80'],
            $prefix.'postal_code' => ['required', 'string', 'max:20'],
            $prefix.'country' => ['required', 'string', 'size:2'],
        ];
    }

    public function rules(): array
    {
        return self::ruleSet() + ['label' => ['nullable', 'string', 'max:40'], 'is_default' => ['nullable', 'boolean']];
    }
}
