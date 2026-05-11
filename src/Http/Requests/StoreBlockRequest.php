<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreBlockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $isUpdate = (bool) $this->route('block');
        $unique = $isUpdate
            ? Rule::unique('blocks', 'slug')->ignore($this->route('block'))
            : Rule::unique('blocks', 'slug');

        return [
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255', $unique],
            'type' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:50'],
            'entity_type' => ['nullable', 'string', 'max:50'],
            'card_type' => ['nullable', 'string', 'max:100'],
            'html_content' => ['nullable', 'string'],
            'filter_config' => ['nullable', 'array'],
            'is_active' => ['nullable', 'boolean'],
            'show_mobile' => ['nullable', 'boolean'],
            'show_desktop' => ['nullable', 'boolean'],
            'source' => ['nullable', 'string', 'max:50'],
            'source_entity_type' => ['nullable', 'string', 'max:50'],
            'source_entity_id' => ['nullable', 'integer'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'items_count' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
