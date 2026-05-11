<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreProConRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['pro', 'con'])],
            'text' => ['required', 'string', 'max:500'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
