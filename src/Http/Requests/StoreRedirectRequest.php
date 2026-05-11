<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $isUpdate = (bool) $this->route('redirect');
        $unique = $isUpdate
            ? Rule::unique('redirects', 'from_slug')->ignore($this->route('redirect'))
            : Rule::unique('redirects', 'from_slug');

        return [
            'name' => ['nullable', 'string', 'max:255'],
            'from_slug' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:500', $unique],
            'to_url' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:2000'],
            'group_id' => ['nullable', 'integer'],
        ];
    }
}
