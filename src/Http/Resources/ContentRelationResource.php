<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Resources;

use App\Models\ContentRelation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContentRelation
 */
final class ContentRelationResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'content_id' => $this->content_id,
            'related_content_id' => $this->related_content_id,
            'related' => $this->whenLoaded('related', fn () => [
                'id' => $this->related->id,
                'type' => $this->related->type,
                'slug' => $this->related->slug,
                'h1' => $this->related->h1,
            ]),
            'relation_type' => $this->relation_type,
            'sort_order' => $this->sort_order,
            'extra' => $this->extra,
        ];
    }
}
