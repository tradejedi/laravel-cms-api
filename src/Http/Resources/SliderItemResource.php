<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Resources;

use App\Models\SliderItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin SliderItem
 */
final class SliderItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slider_location' => $this->slider_location,
            'content_id' => $this->content_id,
            'content' => $this->whenLoaded('content', fn (): ?array => $this->content ? [
                'id' => $this->content->id,
                'slug' => $this->content->slug,
                'h1' => $this->content->h1,
            ] : null),
            'sort_order' => $this->sort_order,
            'is_active' => $this->is_active,
        ];
    }
}
