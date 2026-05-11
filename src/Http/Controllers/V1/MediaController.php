<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Content;
use CoolMacJedi\LaravelCmsApi\Http\Requests\StoreMediaRequest;
use CoolMacJedi\LaravelCmsApi\Http\Resources\MediaResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 *   GET    /posts/{post}/media[?collection=screenshots]
 *   POST   /posts/{post}/media (multipart file | url | base64+filename)
 *   DELETE /media/{id}
 */
final class MediaController extends Controller
{
    public function index(int $post, Request $request): AnonymousResourceCollection
    {
        $content = Content::findOrFail($post);
        $items = $content->media();

        if ($coll = $request->string('collection')->toString()) {
            $items->where('collection_name', $coll);
        }

        return MediaResource::collection($items->orderBy('order_column')->get());
    }

    public function store(int $post, StoreMediaRequest $request): JsonResponse
    {
        $content = Content::findOrFail($post);
        $data = $request->validated();
        $coll = $data['collection'];

        $adder = match (true) {
            $request->hasFile('file') => $content->addMedia($request->file('file')),
            ! empty($data['url']) => $content->addMediaFromUrl($data['url']),
            ! empty($data['base64']) => $content
                ->addMediaFromBase64($this->stripDataUri($data['base64']))
                ->usingFileName($data['filename']),
            default => null,
        };
        if ($adder === null) {
            return response()->json(['errors' => ['file' => 'No usable file source']], 422);
        }

        if (! empty($data['name'])) {
            $adder->usingName($data['name']);
        }

        $media = $adder->toMediaCollection($coll);

        return (new MediaResource($media))->response()->setStatusCode(201);
    }

    public function destroy(int $media): JsonResponse
    {
        Media::findOrFail($media)->delete();

        return response()->json(['deleted' => true, 'id' => $media]);
    }

    /**
     * Spatie's addMediaFromBase64 wants the raw base64, not a data: URI.
     */
    private function stripDataUri(string $b64): string
    {
        if (str_contains($b64, ',')) {
            [, $rest] = explode(',', $b64, 2);

            return $rest;
        }

        return $b64;
    }
}
