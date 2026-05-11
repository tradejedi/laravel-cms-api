<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Redirect;
use CoolMacJedi\LaravelCmsApi\Http\Requests\StoreRedirectRequest;
use CoolMacJedi\LaravelCmsApi\Http\Resources\RedirectResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class RedirectsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $q = Redirect::query();
        if ($search = $request->string('q')->toString()) {
            $q->where(function ($q2) use ($search): void {
                $q2->where('from_slug', 'like', "%{$search}%")->orWhere('to_url', 'like', "%{$search}%");
            });
        }

        return RedirectResource::collection($q->orderByDesc('id')->paginate(min(100, max(1, (int) $request->input('per_page', 50)))));
    }

    public function show(int $redirect): RedirectResource
    {
        return new RedirectResource(Redirect::findOrFail($redirect));
    }

    public function store(StoreRedirectRequest $request): JsonResponse
    {
        return (new RedirectResource(Redirect::create($request->validated())))
            ->response()->setStatusCode(201);
    }

    public function update(int $redirect, StoreRedirectRequest $request): RedirectResource
    {
        $model = Redirect::findOrFail($redirect);
        $model->fill($request->validated())->save();

        return new RedirectResource($model);
    }

    public function destroy(int $redirect): JsonResponse
    {
        Redirect::findOrFail($redirect)->delete();

        return response()->json(['deleted' => true, 'id' => $redirect]);
    }
}
