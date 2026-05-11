<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Comment;
use CoolMacJedi\LaravelCmsApi\Http\Resources\CommentResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

/**
 * Comment moderation surface.
 *
 *   GET    /comments?status=pending|approved|rejected
 *   GET    /comments/{id}
 *   PATCH  /comments/{id}/approve
 *   PATCH  /comments/{id}/reject
 *   DELETE /comments/{id}
 *
 * Create is NOT exposed — comments come from the public site, not the agent.
 */
final class CommentsController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $status = $request->string('status')->toString() ?: 'pending';
        validator(['status' => $status], ['status' => [Rule::in(['pending', 'approved', 'rejected'])]])->validate();

        $q = Comment::with('content')->where('status', $status);
        if ($contentId = $request->integer('content_id')) {
            $q->where('content_id', $contentId);
        }

        return CommentResource::collection(
            $q->orderByDesc('id')->paginate(min(100, max(1, (int) $request->input('per_page', 50))))
        );
    }

    public function show(int $comment): CommentResource
    {
        return new CommentResource(Comment::with('content')->findOrFail($comment));
    }

    public function approve(int $comment): CommentResource
    {
        $c = Comment::findOrFail($comment);
        $c->approve();

        return new CommentResource($c->fresh());
    }

    public function reject(int $comment): CommentResource
    {
        $c = Comment::findOrFail($comment);
        $c->status = 'rejected';
        $c->save();

        return new CommentResource($c->fresh());
    }

    public function destroy(int $comment): JsonResponse
    {
        Comment::findOrFail($comment)->delete();

        return response()->json(['deleted' => true, 'id' => $comment]);
    }
}
