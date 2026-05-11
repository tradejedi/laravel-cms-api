<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Resources;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Comment
 */
final class CommentResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'parent_id' => $this->parent_id,
            'content_id' => $this->content_id,
            'content' => $this->whenLoaded('content', fn (): ?array => $this->content ? [
                'id' => $this->content->id,
                'slug' => $this->content->slug,
                'h1' => $this->content->h1,
            ] : null),
            'author_name' => $this->author_name,
            'author_email' => $this->author_email,
            'text' => $this->text,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
