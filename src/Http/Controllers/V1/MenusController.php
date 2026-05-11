<?php

declare(strict_types=1);

namespace CoolMacJedi\LaravelCmsApi\Http\Controllers\V1;

use App\Models\Menu;
use App\Models\MenuItem;
use CoolMacJedi\LaravelCmsApi\Http\Requests\StoreMenuItemRequest;
use CoolMacJedi\LaravelCmsApi\Http\Requests\StoreMenuRequest;
use CoolMacJedi\LaravelCmsApi\Http\Resources\MenuItemResource;
use CoolMacJedi\LaravelCmsApi\Http\Resources\MenuResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

final class MenusController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return MenuResource::collection(Menu::orderBy('location')->orderBy('name')->get());
    }

    public function show(int $menu): MenuResource
    {
        return new MenuResource(Menu::with(['items' => fn ($q) => $q->orderBy('sort_order')])->findOrFail($menu));
    }

    public function store(StoreMenuRequest $request): JsonResponse
    {
        return (new MenuResource(Menu::create($request->validated())))
            ->response()->setStatusCode(201);
    }

    public function update(int $menu, StoreMenuRequest $request): MenuResource
    {
        $model = Menu::findOrFail($menu);
        $model->fill($request->validated())->save();

        return new MenuResource($model);
    }

    public function destroy(int $menu): JsonResponse
    {
        Menu::findOrFail($menu)->delete();

        return response()->json(['deleted' => true, 'id' => $menu]);
    }

    // ── Menu items nested under a menu ─────────────────────────────────

    public function indexItems(int $menu): AnonymousResourceCollection
    {
        Menu::findOrFail($menu);

        return MenuItemResource::collection(
            MenuItem::where('menu_id', $menu)->orderBy('sort_order')->get()
        );
    }

    public function storeItem(int $menu, StoreMenuItemRequest $request): JsonResponse
    {
        Menu::findOrFail($menu);
        $data = $request->validated();
        $data['menu_id'] = $menu;

        return (new MenuItemResource(MenuItem::create($data)))->response()->setStatusCode(201);
    }

    public function updateItem(int $item, StoreMenuItemRequest $request): MenuItemResource
    {
        $model = MenuItem::findOrFail($item);
        $model->fill($request->validated())->save();

        return new MenuItemResource($model);
    }

    public function destroyItem(int $item): JsonResponse
    {
        MenuItem::findOrFail($item)->delete();

        return response()->json(['deleted' => true, 'id' => $item]);
    }
}
