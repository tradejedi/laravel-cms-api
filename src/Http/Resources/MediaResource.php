<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Spatie MediaLibrary entry — file metadata + URL.
 *
 * @mixin Media
 */
final class MediaResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'collection' => $this->collection_name,
            'file_name' => $this->file_name,
            'name' => $this->name,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'url' => $this->getFullUrl(),
            'thumb' => $this->hasGeneratedConversion('thumb') ? $this->getFullUrl('thumb') : null,
            'preview' => $this->hasGeneratedConversion('preview') ? $this->getFullUrl('preview') : null,
            'card' => $this->hasGeneratedConversion('card') ? $this->getFullUrl('card') : null,
            'order_column' => $this->order_column,
        ];
    }
}
