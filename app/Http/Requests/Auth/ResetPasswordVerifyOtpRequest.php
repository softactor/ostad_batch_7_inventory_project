<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiBaseRequestTrait;
use App\Rules\Auth\ResetPasswordOtpVerifyRule;
use Illuminate\Foundation\Http\FormRequest;

class ResetPasswordVerifyOtpRequest extends FormRequest
{
    use ApiBaseRequestTrait;
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'otp' => [
                'required',
                'digits:6',
                new ResetPasswordOtpVerifyRule($this->email)
            ],
        ];
    }
}
