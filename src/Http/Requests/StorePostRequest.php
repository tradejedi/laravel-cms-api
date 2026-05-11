<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Requests;

use App\Models\Content;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validation + draft enforcement for `POST /api/v1/posts`.
 *
 * is_published is ignored — every API-created post starts as a draft, by
 * deliberate design. To publish, a human goes to the admin and clicks publish.
 */
final class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('contents', 'id')->where('type', Content::TYPE_CATEGORY ?? 'category')],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9-]+$/', 'unique:contents,slug'],
            'h1' => ['required', 'string', 'max:255'],
            'short_title' => ['nullable', 'string', 'max:255'],
            'menu_title' => ['nullable', 'string', 'max:255'],
            'block_title' => ['nullable', 'string', 'max:255'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string'],
            'author_id' => ['nullable', 'integer', 'exists:users,id'],
            'content_author_id' => ['nullable', 'integer', 'exists:authors,id'],
            'is_noindex' => ['nullable', 'boolean'],
            'show_in_menu' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'publish_at' => ['nullable', 'date'],
            'extra' => ['nullable', 'array'],
        ];
    }

    /** @return array<string, mixed> */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated($key, $default);
        $data['type'] = 'post';
        // Drafts only — agent never publishes directly.
        $data['is_published'] = false;

        return $data;
    }
}
