<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChatRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'session_id' => ['required', 'string', 'max:64'],
            'message' => ['required', 'string', 'min:1', 'max:2000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'session_id.required' => 'Session ID wajib diisi.',
            'session_id.max' => 'Session ID maksimal 64 karakter.',
            'message.required' => 'Pesan tidak boleh kosong.',
            'message.min' => 'Pesan minimal 1 karakter.',
            'message.max' => 'Pesan maksimal 2000 karakter.',
        ];
    }

    /**
     * Get the session ID from the request.
     */
    public function getSessionId(): string
    {
        return $this->validated('session_id');
    }

    /**
     * Get the message from the request.
     */
    public function getMessage(): string
    {
        return trim($this->validated('message'));
    }
}
