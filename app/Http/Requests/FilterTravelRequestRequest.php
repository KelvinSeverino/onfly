<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FilterTravelRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status_code' => 'sometimes|string|in:S,A,C',
            'travel_status_id' => 'sometimes|int',
            'destination' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date_format:Y-m-d',
            'end_date' => 'sometimes|date_format:Y-m-d',
        ];
    }
}
