<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreAuthorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $isUpdate = (bool) $this->route('author');
        $unique = $isUpdate
            ? Rule::unique('authors', 'slug')->ignore($this->route('author'))
            : Rule::unique('authors', 'slug');

        return [
            'name' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255'],
            'slug' => [$isUpdate ? 'sometimes' : 'required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', $unique],
            'email' => ['nullable', 'email', 'max:255'],
            'short_bio' => ['nullable', 'string'],
            'rating' => ['nullable', 'integer', 'min:0'],
            'twitter_link' => ['nullable', 'url', 'max:500'],
            'facebook_link' => ['nullable', 'url', 'max:500'],
            'linkedin_link' => ['nullable', 'url', 'max:500'],
            'vk_link' => ['nullable', 'url', 'max:500'],
        ];
    }
}
