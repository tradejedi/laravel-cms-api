<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Content;
use CoolMacJedi\LaravelCmsApi\Http\Requests\StorePostRequest;
use CoolMacJedi\LaravelCmsApi\Http\Requests\UpdatePostRequest;
use CoolMacJedi\LaravelCmsApi\Http\Resources\ContentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Posts CRUD — type=post entries in the contents table.
 *
 * All writes produce drafts (is_published=false). The agent cannot publish.
 *
 * Filters on index:
 *   - category    (string slug)  scope to posts in that category
 *   - q           (string)       fuzzy match on slug or h1
 *   - per_page    (int, max 100) page size (default 20)
 *   - include_drafts (bool)      include is_published=false posts (default true for API)
 */
final class PostsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Content::ofTypePost()->with(['category', 'author']);

        if ($slug = $request->string('category')->toString()) {
            $category = Content::ofTypeCategory()->where('slug', $slug)->first();
            if ($category) {
                $query->where('category_id', $category->id);
            } else {
                // Unknown category — return empty rather than 404 to keep
                // pagination shape predictable for the agent.
                $query->whereRaw('1=0');
            }
        }

        if ($q = $request->string('q')->toString()) {
            $query->where(function ($q2) use ($q): void {
                $q2->where('slug', 'like', "%{$q}%")->orWhere('h1', 'like', "%{$q}%");
            });
        }

        if ($request->boolean('published_only')) {
            $query->where('is_published', true);
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 20)));

        return ContentResource::collection(
            $query->orderByDesc('id')->paginate($perPage)
        );
    }

    public function show(string $key): ContentResource
    {
        $post = is_numeric($key)
            ? Content::ofTypePost()->with(['category', 'author', 'contentAuthor'])->findOrFail((int) $key)
            : Content::ofTypePost()->with(['category', 'author', 'contentAuthor'])->where('slug', $key)->firstOrFail();

        return new ContentResource($post);
    }

    public function store(StorePostRequest $request): JsonResponse
    {
        $post = Content::create($request->validated());
        $post->load(['category', 'author']);

        return (new ContentResource($post))
            ->response()
            ->setStatusCode(201);
    }

    public function update(int $post, UpdatePostRequest $request): ContentResource
    {
        $model = Content::ofTypePost()->findOrFail($post);
        $model->fill($request->validated())->save();
        $model->load(['category', 'author']);

        return new ContentResource($model);
    }

    public function destroy(int $post): JsonResponse
    {
        $model = Content::ofTypePost()->findOrFail($post);
        $model->delete();

        return response()->json(['deleted' => true, 'id' => $post]);
    }
}
