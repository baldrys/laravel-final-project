<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\AddItemToStoreRequest;
use App\Http\Requests\V1\IngredientsRequest;
use App\Http\Transformers\V1\ItemTransformer;
use App\Http\Transformers\V1\OrderTransformer;
use App\Models\Item;
use App\Models\Store;
use App\Support\Enums\OrderStatus;
use Illuminate\Http\Request;

class StoreController extends Controller
{

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function addItemToStore(Store $store, AddItemToStoreRequest $request)
    {
        $item = Item::create([
            'name' => $request->name,
            'store_id' => $store->id,
        ]);
        $ingredients = $request->ingredients;
        if ($ingredients) {
            $item->saveIngredients($ingredients);
        }
        return response()->json([
            "success" => true,
            "data" => [
                "item" => ItemTransformer::transformItem($item),
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateStoreItem(Store $store, Item $item, IngredientsRequest $request)
    {
        $name = $request->name;
        if ($name) {
            $item->name = $name;
            $item->save();
        }

        $ingredients = $request->ingredients;
        if ($ingredients) {
            $item->replaceIngredients($ingredients);
        }

        return response()->json([
            "success" => true,
            "data" => [
                "item" => ItemTransformer::transformItem($item),
            ],
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteStoreItem(Store $store, Item $item, Request $request)
    {
        $orders = $item->orders()->where('status', '<>', OrderStatus::Canceled)->get();

        if ($orders->count() > 0) {
            return response()->json([
                "success" => false,
                "message" => "Невозможно удалить items. Используется в следующих заказах.",
                "orders" => OrderTransformer::transformCollection($orders),
            ], 400);
        }

        $item->delete();
        return response()->json([
            "success" => true,
            "data" => [
                "deleted_item" => ItemTransformer::transformItem($item),
            ],
        ]);

    }

}
