<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * GET /api/v1/whoami
 *
 * Sanity probe + token introspection. The agent calls this once on boot
 * to confirm credentials, learn which site it is talking to, and discover
 * its granted abilities (so it doesn't attempt forbidden actions).
 *
 * Hard-coupled to App\Models\Setting for the per-site name. This is
 * intentional — the package assumes the host app exposes Setting::get('site_name', …).
 */
final class WhoamiController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        $user = $request->user();

        return response()->json([
            'token' => [
                'name' => $token->name,
                'abilities' => $token->abilities,
                'last_used' => $token->last_used_at,
            ],
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
            'site' => [
                'name' => Setting::get('site_name', config('app.name')),
                'url' => config('app.url'),
                'locale' => app()->getLocale(),
                'api_ver' => 'v1',
            ],
        ]);
    }
}
