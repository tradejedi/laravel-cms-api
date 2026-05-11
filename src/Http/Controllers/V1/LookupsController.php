<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Author;
use App\Models\Content;
use App\Models\Country;
use App\Models\Currency;
use App\Models\Language;
use App\Models\License;
use Illuminate\Http\JsonResponse;

/**
 * Short flat lists for foreign-key resolution. The agent calls these once,
 * caches them mentally for a session, and refers to ids/slugs when creating
 * or updating content. All read-only.
 */
final class LookupsController extends Controller
{
    public function categories(): JsonResponse
    {
        $rows = Content::ofTypeCategory()
            ->orderBy('sort_order')->orderBy('h1')
            ->get(['id', 'slug', 'h1'])
            ->map(fn ($c): array => ['id' => $c->id, 'slug' => $c->slug, 'name' => $c->h1]);

        return response()->json(['data' => $rows]);
    }

    public function countries(): JsonResponse
    {
        $rows = Country::query()->orderBy('sort_order')->orderBy('name')
            ->get(['id', 'code', 'name']);

        return response()->json(['data' => $rows]);
    }

    public function currencies(): JsonResponse
    {
        $rows = Currency::query()->orderBy('sort_order')->orderBy('code')
            ->get(['id', 'code', 'name', 'symbol']);

        return response()->json(['data' => $rows]);
    }

    public function languages(): JsonResponse
    {
        $rows = Language::query()->orderBy('sort_order')->orderBy('name')
            ->get(['id', 'code', 'name']);

        return response()->json(['data' => $rows]);
    }

    public function licenses(): JsonResponse
    {
        $rows = License::query()->orderBy('name')
            ->get(['id', 'name']);

        return response()->json(['data' => $rows]);
    }

    public function authors(): JsonResponse
    {
        $rows = Author::query()->orderBy('name')
            ->get(['id', 'slug', 'name']);

        return response()->json(['data' => $rows]);
    }
}
