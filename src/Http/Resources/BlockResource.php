<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Resources;

use App\Models\Block;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Block
 */
final class BlockResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type,
            'entity_type' => $this->entity_type,
            'card_type' => $this->card_type,
            'html_content' => $this->html_content,
            'filter_config' => $this->filter_config,
            'is_active' => $this->is_active,
            'show_mobile' => $this->show_mobile,
            'show_desktop' => $this->show_desktop,
            'source' => $this->source,
            'source_entity_type' => $this->source_entity_type,
            'source_entity_id' => $this->source_entity_id,
            'sort_order' => $this->sort_order,
            'items_count' => $this->items_count,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
