<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Resources;

use App\Models\TableBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin TableBuilder
 */
final class TableBuilderResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'table_type' => $this->table_type,
            'entity_type' => $this->entity_type,
            'columns_count' => $this->columns_count,
            'columns_config' => $this->columns_config,
            'entity_ids' => $this->entity_ids,
            'free_cells' => $this->free_cells,
            'html_content' => $this->html_content,
            'show_on_mobile' => $this->show_on_mobile,
            'show_on_desktop' => $this->show_on_desktop,
            'is_enabled' => $this->is_enabled,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
