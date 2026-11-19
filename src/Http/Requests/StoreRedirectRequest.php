<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
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
            // An absolute http(s) URL or a path on this site ("/casinos/x").
            // A bare "www.site.com" would be sent as a relative Location and
            // land on /go/www.site.com (404). Column is 1000.
            'to_url' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:1000', function (string $attribute, mixed $value, \Closure $fail): void {
                if (! self::isValidTarget((string) $value)) {
                    $fail('to_url must be an absolute http(s) URL (https://example.com/...) or a path starting with "/".');
                }
            }],
            'group_id' => ['nullable', 'integer'],
        ];
    }

    public static function isValidTarget(string $value): bool
    {
        if (preg_match('~^/(?!/)\S*$~', $value)) {
            return true;
        }

        // Same check as Laravel's url:http,https rule (handles IDN and unicode paths).
        return Str::isUrl($value, ['http', 'https']);
    }
}
