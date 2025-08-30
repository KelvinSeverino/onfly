<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTravelRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'destination' => 'sometimes|string|max:255',
            'departure_date' => 'sometimes|date_format:Y-m-d H:i:s',
            'return_date' => 'sometimes|date_format:Y-m-d H:i:s|after_or_equal:departure_date',
        ];
    }
}
