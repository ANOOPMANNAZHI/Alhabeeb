<?php

namespace Modules\MobileApi\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Masters\Entities\Inventory;

/** Read-only inventory master for the "material used" picker. */
class InventoryController extends ApiController
{
    public function index(Request $request)
    {
        $request->validate(['q' => 'nullable|string|max:100', 'updated_since' => 'nullable|date', 'per_page' => 'nullable|integer|max:500']);
        $items = Inventory::query()
            ->when($request->q, fn ($q) => $q->where('inventories_name', 'ilike', '%' . $request->q . '%'))
            ->when($request->updated_since, fn ($q) => $q->where('updated_at', '>=', $request->date('updated_since')))
            ->orderBy('inventories_name')
            ->paginate($request->integer('per_page') ?: 200);

        return response()->json([
            'data' => $items->getCollection()->map(fn ($i) => [
                'id' => $i->id, 'name' => $i->inventories_name, 'brand' => $i->inventories_brand_name, 'description' => $i->inventories_desc, 'updated_at' => $i->updated_at,
            ])->values(),
            'meta' => ['current_page' => $items->currentPage(), 'last_page' => $items->lastPage(), 'per_page' => $items->perPage(), 'total' => $items->total()],
        ]);
    }
}
