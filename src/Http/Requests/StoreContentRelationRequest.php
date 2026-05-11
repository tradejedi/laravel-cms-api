<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

final class StoreContentRelationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'related_content_id' => ['required', 'integer', 'exists:contents,id'],
            'relation_type' => ['required', 'string', 'max:50'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'extra' => ['nullable', 'array'],
        ];
    }

    public function withValidator(Validator $v): void
    {
        $v->after(function ($v): void {
            $postId = (int) $this->route('post');
            if ((int) $this->input('related_content_id') === $postId) {
                $v->errors()->add('related_content_id', 'Cannot relate a content to itself.');
            }
        });
    }
}
