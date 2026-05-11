<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Resources;

use App\Models\ProCon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProCon
 */
final class ProConResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'text' => $this->text,
            'sort_order' => $this->sort_order,
        ];
    }
}
