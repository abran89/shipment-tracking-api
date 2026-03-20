<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CreatePacketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tracking_code'        => ['required', 'string', 'unique:packets,tracking_code'],
            'recipient_name'       => ['required', 'string'],
            'recipient_email'      => ['required', 'email'],
            'destination_address'  => ['required', 'string'],
            'weight_grams'         => ['required', 'integer', 'min:1'],
        ];
    }
}
