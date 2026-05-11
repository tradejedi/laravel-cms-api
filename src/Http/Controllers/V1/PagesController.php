<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Content;
use CoolMacJedi\LaravelCmsApi\Http\Requests\StorePageRequest;
use CoolMacJedi\LaravelCmsApi\Http\Requests\UpdatePageRequest;
use CoolMacJedi\LaravelCmsApi\Http\Resources\ContentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Pages CRUD — type=page entries in the contents table.
 *
 * Like PostsController but no category_id requirement (pages are standalone).
 * Drafts only: is_published forced to false on store.
 */
final class PagesController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $query = Content::ofTypePage();

        if ($q = $request->string('q')->toString()) {
            $query->where(function ($q2) use ($q): void {
                $q2->where('slug', 'like', "%{$q}%")->orWhere('h1', 'like', "%{$q}%");
            });
        }
        if ($request->boolean('published_only')) {
            $query->where('is_published', true);
        }

        $perPage = min(100, max(1, (int) $request->input('per_page', 20)));

        return ContentResource::collection($query->orderByDesc('id')->paginate($perPage));
    }

    public function show(string $key): ContentResource
    {
        $page = is_numeric($key)
            ? Content::ofTypePage()->findOrFail((int) $key)
            : Content::ofTypePage()->where('slug', $key)->firstOrFail();

        return new ContentResource($page);
    }

    public function store(StorePageRequest $request): JsonResponse
    {
        $page = Content::create($request->validated());

        return (new ContentResource($page))->response()->setStatusCode(201);
    }

    public function update(int $page, UpdatePageRequest $request): ContentResource
    {
        $model = Content::ofTypePage()->findOrFail($page);
        $model->fill($request->validated())->save();

        return new ContentResource($model);
    }

    public function destroy(int $page): JsonResponse
    {
        Content::ofTypePage()->findOrFail($page)->delete();

        return response()->json(['deleted' => true, 'id' => $page]);
    }
}
