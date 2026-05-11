<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\TableBuilder;
use CoolMacJedi\LaravelCmsApi\Http\Requests\StoreTableBuilderRequest;
use CoolMacJedi\LaravelCmsApi\Http\Resources\TableBuilderResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class TableBuildersController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return TableBuilderResource::collection(TableBuilder::orderBy('id')->get());
    }

    public function show(string $key): TableBuilderResource
    {
        $row = is_numeric($key)
            ? TableBuilder::findOrFail((int) $key)
            : TableBuilder::where('slug', $key)->firstOrFail();

        return new TableBuilderResource($row);
    }

    public function store(StoreTableBuilderRequest $request): JsonResponse
    {
        return (new TableBuilderResource(TableBuilder::create($request->validated())))
            ->response()->setStatusCode(201);
    }

    public function update(int $table, StoreTableBuilderRequest $request): TableBuilderResource
    {
        $model = TableBuilder::findOrFail($table);
        $model->fill($request->validated())->save();

        return new TableBuilderResource($model);
    }

    public function destroy(int $table): JsonResponse
    {
        TableBuilder::findOrFail($table)->delete();

        return response()->json(['deleted' => true, 'id' => $table]);
    }
}
