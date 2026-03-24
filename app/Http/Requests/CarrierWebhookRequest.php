<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CarrierWebhookRequest extends FormRequest
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
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tracking_code' => ['required', 'string'],
            'status'        => ['required', 'in:delivered'],
            'timestamp'     => ['required', 'date'],
            'signature'     => ['required', 'string'],
        ];
    }

    public function isValidSignature(): bool
    {
        $payload = $this->except('signature');
        $expected = 'sha256=' . hash_hmac('sha256', json_encode($payload), config('services.carrier_webhook_secret'));

        return hash_equals($expected, $this->input('signature'));
    }
}
