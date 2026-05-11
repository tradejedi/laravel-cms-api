<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Content;
use App\Models\ContentParam;
use App\Models\ContentSchema;
use CoolMacJedi\LaravelCmsApi\Http\Requests\UpsertContentParamRequest;
use CoolMacJedi\LaravelCmsApi\Http\Resources\ContentParamResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Per-post extra fields driven by ContentSchema rows of the post's category.
 *
 *   GET     /api/v1/posts/{post}/params           — list values
 *   PATCH   /api/v1/posts/{post}/params/{key}     — upsert by schema key
 *   DELETE  /api/v1/posts/{post}/params/{key}     — remove
 *
 * Keys correspond to ContentSchema.key for the post's category. The endpoint
 * routes value vs ref_content_id by checking the schema's param_kind.
 */
final class ContentParamsController extends Controller
{
    public function index(int $post): AnonymousResourceCollection
    {
        $content = Content::ofTypePost()->findOrFail($post);
        $params = ContentParam::with(['schema', 'refContent'])
            ->where('content_id', $content->id)
            ->orderBy('sort_order')->get();

        return ContentParamResource::collection($params);
    }

    public function upsert(int $post, string $key, UpsertContentParamRequest $request): JsonResponse
    {
        $content = Content::ofTypePost()->findOrFail($post);
        $schema = ContentSchema::where('category_id', $content->category_id)
            ->where('key', $key)
            ->firstOrFail();

        $data = $request->validated();
        // Direct param_kind from schema decides which column to use.
        $payload = [
            'sort_order' => $data['sort_order'] ?? 0,
        ];
        if ($schema->param_kind === 'page') {
            $payload['ref_content_id'] = $data['ref_content_id'] ?? null;
            $payload['value'] = null;
            if ($payload['ref_content_id'] === null) {
                return response()->json(['errors' => ['ref_content_id' => ['Required for param_kind=page']]], 422);
            }
        } else {
            $payload['value'] = $data['value'] ?? null;
            $payload['ref_content_id'] = null;
        }

        $param = ContentParam::updateOrCreate(
            ['content_id' => $content->id, 'schema_id' => $schema->id],
            $payload
        );
        $param->load(['schema', 'refContent']);

        return (new ContentParamResource($param))->response();
    }

    public function destroy(int $post, string $key): JsonResponse
    {
        $content = Content::ofTypePost()->findOrFail($post);
        $schema = ContentSchema::where('category_id', $content->category_id)
            ->where('key', $key)
            ->firstOrFail();

        $deleted = ContentParam::where('content_id', $content->id)
            ->where('schema_id', $schema->id)
            ->delete();

        return response()->json(['deleted' => (bool) $deleted, 'key' => $key]);
    }
}
