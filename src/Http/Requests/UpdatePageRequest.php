<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdatePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'slug' => ['sometimes', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('contents', 'slug')->ignore($this->route('page'))],
            'h1' => ['sometimes', 'string', 'max:255'],
            'short_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'menu_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'content' => ['sometimes', 'nullable', 'string'],
            'is_noindex' => ['sometimes', 'boolean'],
            'show_in_menu' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'url_prefix' => ['sometimes', 'nullable', 'string', 'max:255'],
            'extra' => ['sometimes', 'nullable', 'array'],
        ];
    }

    /** @return array<string, mixed> */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        unset($data['is_published'], $data['type']);

        return $data;
    }
}
