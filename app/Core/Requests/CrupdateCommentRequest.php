<?php

namespace App\Core\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrupdateCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request
     */
    public function authorize(): bool
    {
        return true; // Authorization handled in controller
    }

    /**
     * Get the validation rules that apply to the request
     */
    public function rules(): array
    {
        return [
            'content' => 'required|string|min:3|max:5000',
            'commentable_type' => 'required|string',
            'commentable_id' => 'required|integer',
            'inReplyTo' => 'nullable|array',
            'inReplyTo.id' => 'integer|required_with:inReplyTo',
            'inReplyTo.user' => 'array|required_with:inReplyTo',
            'inReplyTo.user.id' => 'integer|required_with:inReplyTo',
        ];
    }

    /**
     * Get custom messages for validation rules
     */
    public function messages(): array
    {
        return [
            'content.required' => 'Comment content is required',
            'content.min' => 'Comment must be at least 3 characters',
            'content.max' => 'Comment cannot exceed 5000 characters',
            'commentable_type.required' => 'Commentable type is required',
            'commentable_id.required' => 'Commentable ID is required',
        ];
    }
}
