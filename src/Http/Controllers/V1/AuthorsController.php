<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Author;
use CoolMacJedi\LaravelCmsApi\Http\Requests\StoreAuthorRequest;
use CoolMacJedi\LaravelCmsApi\Http\Requests\StoreMediaRequest;
use CoolMacJedi\LaravelCmsApi\Http\Resources\AuthorResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class AuthorsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $q = Author::query();
        if ($search = $request->string('q')->toString()) {
            $q->where('name', 'like', "%{$search}%");
        }

        return AuthorResource::collection($q->orderBy('name')->get());
    }

    public function show(string $key): AuthorResource
    {
        $author = is_numeric($key)
            ? Author::findOrFail((int) $key)
            : Author::where('slug', $key)->firstOrFail();

        return new AuthorResource($author);
    }

    public function store(StoreAuthorRequest $request): JsonResponse
    {
        return (new AuthorResource(Author::create($request->validated())))
            ->response()->setStatusCode(201);
    }

    public function update(int $author, StoreAuthorRequest $request): AuthorResource
    {
        $model = Author::findOrFail($author);
        $model->fill($request->validated())->save();

        return new AuthorResource($model);
    }

    public function destroy(int $author): JsonResponse
    {
        Author::findOrFail($author)->delete();

        return response()->json(['deleted' => true, 'id' => $author]);
    }

    public function uploadAvatar(int $author, StoreMediaRequest $request): JsonResponse
    {
        $model = Author::findOrFail($author);
        $data = $request->validated();
        // Author only has 'avatar' collection — ignore client-provided collection.
        $adder = match (true) {
            $request->hasFile('file') => $model->addMedia($request->file('file')),
            ! empty($data['url']) => $model->addMediaFromUrl($data['url']),
            ! empty($data['base64']) => $model->addMediaFromBase64(
                str_contains($data['base64'], ',') ? explode(',', $data['base64'], 2)[1] : $data['base64']
            )->usingFileName($data['filename']),
            default => null,
        };
        if ($adder === null) {
            return response()->json(['errors' => ['file' => 'No usable file source']], 422);
        }
        if (! empty($data['name'])) {
            $adder->usingName($data['name']);
        }
        $model->clearMediaCollection('avatar');
        $adder->toMediaCollection('avatar');

        return (new AuthorResource($model->fresh()))->response();
    }
}
