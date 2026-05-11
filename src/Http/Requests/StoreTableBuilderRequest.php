<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreTableBuilderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $isUpdate = (bool) $this->route('table');
        $unique = $isUpdate
            ? Rule::unique('table_builders', 'slug')->ignore($this->route('table'))
            : Rule::unique('table_builders', 'slug');

        return [
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255', $unique],
            'table_type' => ['nullable', 'string', 'max:50'],
            'entity_type' => ['nullable', 'string', 'max:50'],
            'columns_count' => ['nullable', 'integer', 'min:1', 'max:20'],
            'columns_config' => ['nullable', 'array'],
            'entity_ids' => ['nullable', 'array'],
            'free_cells' => ['nullable'],
            'html_content' => ['nullable', 'string'],
            'show_on_mobile' => ['nullable', 'boolean'],
            'show_on_desktop' => ['nullable', 'boolean'],
            'is_enabled' => ['nullable', 'boolean'],
        ];
    }
}
