<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Resources;

use App\Models\Content;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Shared output shape for posts, pages, and categories — same Content model,
 * just different type. Eager-load `category` and `author` before calling for
 * efficient serialization.
 *
 * @mixin Content
 */
final class ContentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'category' => $this->whenLoaded('category', fn (): array => [
                'id' => $this->category->id,
                'slug' => $this->category->slug,
                'h1' => $this->category->h1,
            ]),
            'category_id' => $this->category_id,
            'slug' => $this->slug,
            'h1' => $this->h1,
            'short_title' => $this->short_title,
            'menu_title' => $this->menu_title,
            'block_title' => $this->block_title,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'content' => $this->content,
            'author' => $this->whenLoaded('author', fn (): ?array => $this->author ? [
                'id' => $this->author->id,
                'name' => $this->author->name,
                'email' => $this->author->email,
            ] : null),
            'content_author' => $this->whenLoaded('contentAuthor', fn (): ?array => $this->contentAuthor ? [
                'id' => $this->contentAuthor->id,
                'slug' => $this->contentAuthor->slug,
                'name' => $this->contentAuthor->name,
            ] : null),
            'is_published' => $this->is_published,
            'publish_at' => $this->publish_at,
            'is_noindex' => $this->is_noindex,
            'show_in_menu' => $this->show_in_menu,
            'sort_order' => $this->sort_order,
            'views_count' => $this->views_count,
            'url_prefix' => $this->url_prefix,
            'per_page' => $this->per_page,
            'extra' => $this->extra,
            'url' => $this->getUrl(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
