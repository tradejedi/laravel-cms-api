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
            'from_slug' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255', $unique],
            // Absolute http(s) only: a bare "www.site.com" would be sent as a
            // relative Location and land on /go/www.site.com (404). Column is 1000.
            'to_url' => [$isUpdate ? 'sometimes' : 'required', 'string', 'url:http,https', 'max:1000'],
            'group_id' => ['nullable', 'integer'],
        ];
    }
}
