<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TravelRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requester_name' => 'required|string|max:255',
            'destination' => 'required|string|max:255',
            'departure_date' => 'required|date',
            'return_date' => 'required|date|after_or_equal:departure_date',
            'status' => 'required|string|in:s,a,c',
        ];
    }
}
