<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Content;
use App\Models\Faq;
use CoolMacJedi\LaravelCmsApi\Http\Requests\StoreFaqRequest;
use CoolMacJedi\LaravelCmsApi\Http\Requests\UpdateFaqRequest;
use CoolMacJedi\LaravelCmsApi\Http\Resources\FaqResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Faqs scoped to a Content via polymorphic faqable relation.
 *
 *   GET    /posts/{post}/faqs
 *   POST   /posts/{post}/faqs
 *   PATCH  /faqs/{faq}
 *   DELETE /faqs/{faq}
 */
final class FaqsController extends Controller
{
    public function index(int $post): AnonymousResourceCollection
    {
        $content = Content::findOrFail($post);

        return FaqResource::collection(
            $content->faqs()->ordered()->get()
        );
    }

    public function store(int $post, StoreFaqRequest $request): JsonResponse
    {
        $content = Content::findOrFail($post);

        $faq = Faq::create([
            'faqable_type' => $content::class,
            'faqable_id' => $content->id,
            'content_id' => $content->id,
            'question' => $request->validated()['question'],
            'answer' => $request->validated()['answer'],
            'sort_order' => $request->validated()['sort_order'] ?? 0,
        ]);

        return (new FaqResource($faq))->response()->setStatusCode(201);
    }

    public function update(int $faq, UpdateFaqRequest $request): FaqResource
    {
        $model = Faq::findOrFail($faq);
        $model->fill($request->validated())->save();

        return new FaqResource($model);
    }

    public function destroy(int $faq): JsonResponse
    {
        Faq::findOrFail($faq)->delete();

        return response()->json(['deleted' => true, 'id' => $faq]);
    }
}
