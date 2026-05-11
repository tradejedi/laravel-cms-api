<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Body for `PATCH /api/v1/posts/{id}/params/{key}` — upserts a single param.
 *
 * Shape:
 *   {"value": "96.5"}              // for param_kind=value
 *   {"ref_content_id": 123}        // for param_kind=page
 *
 * One of value/ref_content_id is required (the controller cross-checks against
 * the schema's param_kind).
 */
final class UpsertContentParamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'value' => ['nullable', 'string'],
            'ref_content_id' => ['nullable', 'integer', 'exists:contents,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
