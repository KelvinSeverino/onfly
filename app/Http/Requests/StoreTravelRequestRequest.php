<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTravelRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requester_id' => 'sometimes|required|int',
            'destination' => 'required|string|max:255',
            'departure_date' => 'required|date_format:Y-m-d H:i:s',
            'return_date' => 'required|date_format:Y-m-d H:i:s|after_or_equal:departure_date',
        ];
    }
}
