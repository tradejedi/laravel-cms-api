<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Resources;

use App\Models\MenuItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin MenuItem
 */
final class MenuItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'menu_id' => $this->menu_id,
            'parent_id' => $this->parent_id,
            'type' => $this->type,
            'url' => $this->url,
            'anchor' => $this->anchor,
            'css_class' => $this->css_class,
            'custom_attributes' => $this->custom_attributes,
            'content_id' => $this->content_id,
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];
    }
}
