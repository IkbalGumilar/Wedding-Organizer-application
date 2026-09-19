<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'wedding_package_id' => [
                'required',
                'integer',
                Rule::exists('wedding_packages', 'id')->where(fn ($query) => $query->where('is_active', true)),
            ],
            'event_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'couple_name' => ['required', 'string', 'max:150'],
            'event_location' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
