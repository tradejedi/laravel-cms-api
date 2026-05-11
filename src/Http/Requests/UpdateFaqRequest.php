<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'question' => ['sometimes', 'string', 'max:500'],
            'answer' => ['sometimes', 'string'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
