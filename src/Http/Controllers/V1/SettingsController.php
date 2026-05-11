<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Setting;
use CoolMacJedi\LaravelCmsApi\Http\Requests\UpsertSettingRequest;
use CoolMacJedi\LaravelCmsApi\Http\Resources\SettingResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Generic site settings. Keys are blocked by prefix — traffic-filter
 * settings (cloaka) are NEVER exposed via the API, no matter the scope.
 *
 *   GET    /settings              list allowed
 *   GET    /settings/{key}        one
 *   PATCH  /settings/{key}        upsert
 *   DELETE /settings/{key}        remove
 */
final class SettingsController extends Controller
{
    /**
     * Keys starting with any of these are denied. The traffic-filter package
     * stores its config under these prefixes; managing cloaking via the
     * agent API is out of scope by design.
     */
    private const DENIED_PREFIXES = [
        'traffic.',
        'flow.',
        'cloak.',
        'domain.',
        '_global_filter.',
        'filter.',
    ];

    public function index(): AnonymousResourceCollection
    {
        $rows = Setting::query()->orderBy('key')->get()
            ->reject(fn (Setting $s) => $this->isDenied($s->key));

        return SettingResource::collection($rows);
    }

    public function show(string $key): JsonResponse|SettingResource
    {
        if ($this->isDenied($key)) {
            return response()->json(['errors' => ['key' => ['This setting key is not accessible via API']]], 403);
        }
        $s = Setting::where('key', $key)->firstOrFail();

        return new SettingResource($s);
    }

    public function upsert(string $key, UpsertSettingRequest $request): JsonResponse|SettingResource
    {
        if ($this->isDenied($key)) {
            return response()->json(['errors' => ['key' => ['This setting key is not accessible via API']]], 403);
        }
        $setting = Setting::updateOrCreate(['key' => $key], ['value' => $request->validated()['value'] ?? null]);

        return new SettingResource($setting);
    }

    public function destroy(string $key): JsonResponse
    {
        if ($this->isDenied($key)) {
            return response()->json(['errors' => ['key' => ['This setting key is not accessible via API']]], 403);
        }
        Setting::where('key', $key)->delete();

        return response()->json(['deleted' => true, 'key' => $key]);
    }

    private function isDenied(string $key): bool
    {
        foreach (self::DENIED_PREFIXES as $prefix) {
            if (str_starts_with($key, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
