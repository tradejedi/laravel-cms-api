<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Content;
use CoolMacJedi\LaravelCmsApi\Http\Resources\ContentResource;
use CoolMacJedi\LaravelCmsApi\Http\Resources\ContentSchemaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Read-only category index and the schema-introspection endpoint —
 * the agent uses this to know which params/fields belong to posts in a
 * given category (RTP for slots, license for casinos, etc.).
 *
 * Category writes (creating a new category) are out of scope for Day 2 —
 * they require composite ContentSchema creation. Will revisit in Day 6.
 */
final class CategoriesController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return ContentResource::collection(
            Content::ofTypeCategory()->orderBy('sort_order')->orderBy('h1')->get()
        );
    }

    public function show(string $slug): ContentResource
    {
        $category = Content::ofTypeCategory()->where('slug', $slug)->firstOrFail();

        return new ContentResource($category);
    }

    public function schema(string $slug): JsonResponse
    {
        $category = Content::ofTypeCategory()->with(['schemas' => fn ($q) => $q->orderBy('sort_order')])->where('slug', $slug)->firstOrFail();

        return response()->json([
            'category' => [
                'id' => $category->id,
                'slug' => $category->slug,
                'h1' => $category->h1,
            ],
            'fields' => ContentSchemaResource::collection($category->schemas)->toArray(request()),
        ]);
    }
}
