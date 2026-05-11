<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Partial-update rules for `PATCH /api/v1/posts/{id}`.
 *
 * is_published cannot be set true via API — that's a human decision. If sent,
 * it's stripped from validated() so the underlying value is preserved.
 */
final class UpdatePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $postId = $this->route('post');

        return [
            'category_id' => ['sometimes', 'integer', Rule::exists('contents', 'id')->where('type', 'category')],
            'slug' => ['sometimes', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', Rule::unique('contents', 'slug')->ignore($postId)],
            'h1' => ['sometimes', 'string', 'max:255'],
            'short_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'menu_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'block_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_title' => ['sometimes', 'nullable', 'string', 'max:255'],
            'meta_description' => ['sometimes', 'nullable', 'string', 'max:1000'],
            'content' => ['sometimes', 'nullable', 'string'],
            'author_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'content_author_id' => ['sometimes', 'nullable', 'integer', 'exists:authors,id'],
            'is_noindex' => ['sometimes', 'boolean'],
            'show_in_menu' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
            'publish_at' => ['sometimes', 'nullable', 'date'],
            'extra' => ['sometimes', 'nullable', 'array'],
        ];
    }

    /** @return array<string, mixed> */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        // Strip is_published if someone tries to send it; only the admin UI
        // can flip a draft to published.
        unset($data['is_published'], $data['type']);

        return $data;
    }
}
