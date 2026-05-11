<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreSliderItemRequest extends FormRequest
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
            'slider_location' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:50'],
            'content_id' => [$isUpdate ? 'sometimes' : 'required', 'integer', 'exists:contents,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
