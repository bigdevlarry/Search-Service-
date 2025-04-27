<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CoordinatesRequest extends FormRequest
{
    /** @note determines if user is authorized */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lat' => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ];
    }
}
