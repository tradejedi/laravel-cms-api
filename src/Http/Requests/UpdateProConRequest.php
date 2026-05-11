<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateProConRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['sometimes', Rule::in(['pro', 'con'])],
            'text' => ['sometimes', 'string', 'max:500'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
