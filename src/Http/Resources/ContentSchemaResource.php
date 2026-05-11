<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Resources;

use App\Models\ContentSchema;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Per-category field descriptor. Returned in `GET /categories/{slug}/schema`
 * so the agent knows what extra params it can/should set on posts in that
 * category (RTP, volatility, bonus_amount, etc.).
 *
 * @mixin ContentSchema
 */
final class ContentSchemaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'name' => $this->name,
            'value_type' => $this->value_type,
            'param_kind' => $this->param_kind,
            'options' => $this->options,
            'suffix' => $this->suffix,
            'icon' => $this->icon,
            'is_required' => $this->is_required,
            'is_filterable' => $this->is_filterable,
            'is_visible' => $this->is_visible,
            'sort_order' => $this->sort_order,
        ];
    }
}
