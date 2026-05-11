<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Media upload — accepts one of:
 *   - multipart 'file'        (preferred for binary)
 *   - {"url": "https://..."}  (server fetches it)
 *   - {"base64": "<...>", "filename": "shot.jpg"}  (inline payload)
 *
 * Collection is one of the Content::registerMediaCollections() entries
 * (logo / thumbnail / screenshots / icon). For singleFile collections,
 * an upload replaces the existing file.
 */
final class StoreMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'collection' => ['required', Rule::in(['logo', 'thumbnail', 'screenshots', 'icon'])],
            'file' => ['nullable', 'file', 'mimes:jpeg,jpg,png,webp,svg', 'max:8192'],
            'url' => ['nullable', 'url:http,https', 'max:2000'],
            'base64' => ['nullable', 'string'],
            'filename' => ['nullable', 'string', 'max:200'],
            'name' => ['nullable', 'string', 'max:200'],
        ];
    }

    public function withValidator(Validator $v): void
    {
        $v->after(function ($v): void {
            $sources = array_filter([
                $this->hasFile('file'),
                (bool) $this->input('url'),
                (bool) $this->input('base64'),
            ]);
            if (count($sources) === 0) {
                $v->errors()->add('file', 'One of file / url / base64 is required.');
            }
            if (count($sources) > 1) {
                $v->errors()->add('file', 'Exactly one of file / url / base64 must be set.');
            }
            if ($this->input('base64') && ! $this->input('filename')) {
                $v->errors()->add('filename', 'filename is required when sending base64.');
            }
        });
    }
}
