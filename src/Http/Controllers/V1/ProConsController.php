<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Content;
use App\Models\ProCon;
use CoolMacJedi\LaravelCmsApi\Http\Requests\StoreProConRequest;
use CoolMacJedi\LaravelCmsApi\Http\Requests\UpdateProConRequest;
use CoolMacJedi\LaravelCmsApi\Http\Resources\ProConResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 *   GET    /posts/{post}/pro-cons?type=pro|con
 *   POST   /posts/{post}/pro-cons   {type, text}
 *   PATCH  /pro-cons/{id}
 *   DELETE /pro-cons/{id}
 */
final class ProConsController extends Controller
{
    public function index(int $post, Request $request): AnonymousResourceCollection
    {
        $content = Content::findOrFail($post);
        $q = $content->proCons()->ordered();

        if ($type = $request->string('type')->toString()) {
            $q->where('type', $type);
        }

        return ProConResource::collection($q->get());
    }

    public function store(int $post, StoreProConRequest $request): JsonResponse
    {
        $content = Content::findOrFail($post);

        // content_id is not in ProCon's fillable; assign directly so the
        // Content::proCons() hasMany relation (keyed on content_id) finds it.
        $pc = new ProCon([
            'proconable_type' => $content::class,
            'proconable_id' => $content->id,
            'type' => $request->validated()['type'],
            'text' => $request->validated()['text'],
            'sort_order' => $request->validated()['sort_order'] ?? 0,
        ]);
        $pc->content_id = $content->id;
        $pc->save();

        return (new ProConResource($pc))->response()->setStatusCode(201);
    }

    public function update(int $proCon, UpdateProConRequest $request): ProConResource
    {
        $model = ProCon::findOrFail($proCon);
        $model->fill($request->validated())->save();

        return new ProConResource($model);
    }

    public function destroy(int $proCon): JsonResponse
    {
        ProCon::findOrFail($proCon)->delete();

        return response()->json(['deleted' => true, 'id' => $proCon]);
    }
}
