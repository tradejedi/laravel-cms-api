<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Content;
use App\Models\ContentRelation;
use CoolMacJedi\LaravelCmsApi\Http\Requests\StoreContentRelationRequest;
use CoolMacJedi\LaravelCmsApi\Http\Resources\ContentRelationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 *   GET    /posts/{post}/relations?type=bonus
 *   POST   /posts/{post}/relations   {related_content_id, relation_type}
 *   DELETE /relations/{id}
 */
final class ContentRelationsController extends Controller
{
    public function index(int $post, Request $request): AnonymousResourceCollection
    {
        $content = Content::findOrFail($post);
        $q = ContentRelation::with('related')
            ->where('content_id', $content->id)
            ->orderBy('sort_order');

        if ($type = $request->string('type')->toString()) {
            $q->where('relation_type', $type);
        }

        return ContentRelationResource::collection($q->get());
    }

    public function store(int $post, StoreContentRelationRequest $request): JsonResponse
    {
        $content = Content::findOrFail($post);
        $data = $request->validated();

        $rel = ContentRelation::firstOrCreate(
            [
                'content_id' => $content->id,
                'related_content_id' => $data['related_content_id'],
                'relation_type' => $data['relation_type'],
            ],
            [
                'sort_order' => $data['sort_order'] ?? 0,
                'extra' => $data['extra'] ?? null,
            ]
        );
        $rel->load('related');

        return (new ContentRelationResource($rel))->response()->setStatusCode($rel->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(int $relation): JsonResponse
    {
        ContentRelation::findOrFail($relation)->delete();

        return response()->json(['deleted' => true, 'id' => $relation]);
    }
}
