<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Resources;

use App\Models\ContentParam;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ContentParam
 */
final class ContentParamResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'key' => $this->whenLoaded('schema', fn () => $this->schema->key),
            'name' => $this->whenLoaded('schema', fn () => $this->schema->name),
            'value_type' => $this->whenLoaded('schema', fn () => $this->schema->value_type),
            'param_kind' => $this->whenLoaded('schema', fn () => $this->schema->param_kind),
            'value' => $this->value,
            'ref_content_id' => $this->ref_content_id,
            'ref_content' => $this->whenLoaded('refContent', fn () => $this->refContent ? [
                'id' => $this->refContent->id,
                'slug' => $this->refContent->slug,
                'h1' => $this->refContent->h1,
            ] : null),
            'sort_order' => $this->sort_order,
        ];
    }
}
