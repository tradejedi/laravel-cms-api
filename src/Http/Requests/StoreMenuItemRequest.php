<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $isUpdate = (bool) $this->route('item');

        return [
            'parent_id' => ['nullable', 'integer', 'exists:menu_items,id'],
            'type' => [$isUpdate ? 'sometimes' : 'required', Rule::in(['content', 'url', 'anchor'])],
            'url' => ['nullable', 'string', 'max:500'],
            'anchor' => ['nullable', 'string', 'max:255'],
            'css_class' => ['nullable', 'string', 'max:100'],
            'custom_attributes' => ['nullable', 'string', 'max:500'],
            'content_id' => ['nullable', 'integer', 'exists:contents,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
