<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Block;
use CoolMacJedi\LaravelCmsApi\Http\Requests\StoreBlockRequest;
use CoolMacJedi\LaravelCmsApi\Http\Resources\BlockResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class BlocksController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $q = Block::query();
        if ($type = $request->string('type')->toString()) {
            $q->where('type', $type);
        }

        return BlockResource::collection($q->orderBy('sort_order')->orderBy('id')->get());
    }

    public function show(int $block): BlockResource
    {
        return new BlockResource(Block::findOrFail($block));
    }

    public function store(StoreBlockRequest $request): JsonResponse
    {
        return (new BlockResource(Block::create($request->validated())))
            ->response()->setStatusCode(201);
    }

    public function update(int $block, StoreBlockRequest $request): BlockResource
    {
        $model = Block::findOrFail($block);
        $model->fill($request->validated())->save();

        return new BlockResource($model);
    }

    public function destroy(int $block): JsonResponse
    {
        Block::findOrFail($block)->delete();

        return response()->json(['deleted' => true, 'id' => $block]);
    }
}
