<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\SliderItem;
use CoolMacJedi\LaravelCmsApi\Http\Requests\StoreSliderItemRequest;
use CoolMacJedi\LaravelCmsApi\Http\Resources\SliderItemResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class SliderItemsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $q = SliderItem::with('content');
        if ($loc = $request->string('location')->toString()) {
            $q->where('slider_location', $loc);
        }

        return SliderItemResource::collection($q->orderBy('sort_order')->get());
    }

    public function store(StoreSliderItemRequest $request): JsonResponse
    {
        $item = SliderItem::create($request->validated());
        $item->load('content');

        return (new SliderItemResource($item))->response()->setStatusCode(201);
    }

    public function update(int $item, StoreSliderItemRequest $request): SliderItemResource
    {
        $model = SliderItem::findOrFail($item);
        $model->fill($request->validated())->save();
        $model->load('content');

        return new SliderItemResource($model);
    }

    public function destroy(int $item): JsonResponse
    {
        SliderItem::findOrFail($item)->delete();

        return response()->json(['deleted' => true, 'id' => $item]);
    }
}
