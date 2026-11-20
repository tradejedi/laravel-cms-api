<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Content;
use App\Support\ContentBulk;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use InvalidArgumentException;

/**
 * Bulk actions on posts or pages: one request, one transaction.
 *
 *   POST /api/v1/posts/bulk   {"action": "...", "ids": [1, 2, 3], "data": {...}, "category": "news"}
 *   POST /api/v1/pages/bulk   {"action": "...", "ids": [1, 2, 3], "data": {...}}
 *
 * Actions and the token ability each one needs:
 *   - update        content:write   set the same fields on every record (see ContentBulk::UPDATABLE)
 *   - unpublish     content:write   back to draft
 *   - restore       content:write   bring back from the trash
 *   - delete        content:delete  move to the trash (the home page is never deleted)
 *   - force_delete  content:delete  delete forever records that are already in the trash
 *
 * There is no `publish`: the API only produces drafts, a person publishes in the admin.
 * Up to 500 ids per request. Ids that are not of this type, not in `category`
 * (when given) or not in the right trash state are returned in `skipped`.
 */
final class BulkContentController extends Controller
{
    public const API_ACTIONS = ['update', 'unpublish', 'restore', 'delete', 'force_delete'];

    public function posts(Request $request, ContentBulk $bulk): JsonResponse
    {
        $data = $this->validated($request, Content::TYPE_POST);

        $scope = Content::ofTypePost();
        if (! empty($data['category'])) {
            $category = Content::ofTypeCategory()->where('slug', $data['category'])->firstOrFail();
            $scope->where('category_id', $category->id);
        }

        return $this->run($request, $bulk, $scope, $data);
    }

    public function pages(Request $request, ContentBulk $bulk): JsonResponse
    {
        return $this->run($request, $bulk, Content::ofTypePage(), $this->validated($request, Content::TYPE_PAGE));
    }

    /** @return array<string, mixed> */
    private function validated(Request $request, string $type): array
    {
        $fields = array_values(array_diff(ContentBulk::UPDATABLE, $type === Content::TYPE_POST ? [] : ['category_id']));

        $validated = $request->validate([
            'action' => ['required', 'string', Rule::in(self::API_ACTIONS)],
            'ids' => ['required', 'array', 'min:1', 'max:'.ContentBulk::MAX_IDS],
            'ids.*' => ['integer', 'min:1'],
            'category' => ['sometimes', 'nullable', 'string'],
            'data' => ['required_if:action,update', 'array', 'min:1'],
            'data.is_noindex' => ['sometimes', 'boolean'],
            'data.show_in_menu' => ['sometimes', 'boolean'],
            'data.sort_order' => ['sometimes', 'integer', 'min:0'],
            'data.author_id' => ['sometimes', 'nullable', 'integer', 'exists:users,id'],
            'data.content_author_id' => ['sometimes', 'nullable', 'integer', 'exists:authors,id'],
            'data.publish_at' => ['sometimes', 'nullable', 'date'],
            'data.category_id' => ['sometimes', 'integer', Rule::exists('contents', 'id')->where('type', Content::TYPE_CATEGORY)],
        ]);

        $unknown = array_diff(array_keys($validated['data'] ?? []), $fields);
        $extra = array_diff(array_keys((array) $request->input('data', [])), $fields);
        if ($unknown !== [] || $extra !== []) {
            abort(response()->json([
                'message' => 'Only these fields can be set in bulk: '.implode(', ', $fields).'.',
                'errors' => ['data' => ['Unsupported fields: '.implode(', ', array_unique([...$unknown, ...$extra])).'.']],
            ], 422));
        }

        return $validated;
    }

    /** @param  Builder<Content>  $scope */
    private function run(Request $request, ContentBulk $bulk, Builder $scope, array $data): JsonResponse
    {
        $ability = ContentBulk::ACTIONS[$data['action']];
        abort_unless($request->user()?->tokenCan($ability), 403, "This action needs the {$ability} ability.");

        try {
            $result = $bulk->apply($scope, $data['ids'], $data['action'], $data['data'] ?? []);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json($result + [
            'affected_count' => count($result['affected']),
            'skipped_count' => count($result['skipped']),
        ]);
    }
}
