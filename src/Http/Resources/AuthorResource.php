<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Resources;

use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Author
 */
final class AuthorResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $avatar = $this->getFirstMediaUrl('avatar');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'email' => $this->email,
            'short_bio' => $this->short_bio,
            'rating' => $this->rating,
            'twitter_link' => $this->twitter_link,
            'facebook_link' => $this->facebook_link,
            'linkedin_link' => $this->linkedin_link,
            'vk_link' => $this->vk_link,
            'avatar_url' => $avatar !== '' ? $avatar : null,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
