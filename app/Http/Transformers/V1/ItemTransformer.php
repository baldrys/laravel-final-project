<?php

namespace App\Http\Transformers\V1;

use App\Models\Item;
use App\Models\Ingredient;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use Spatie\Fractalistic\ArraySerializer;

class ItemTransformer extends TransformerAbstract
{
    /**
     * @param Item $item
     * @return array
     */
    public function transform(Item $item)
    {
        $itemIngredients = $item->itemIngredients()->get();
        return [
            'id' => (int) $item->id,
            'store_id' => $item->store_id,
            'name' => $item->name,
            'ingredients' => ItemIngredientTransformer::transformCollection($itemIngredients),

        ];
    }
    /**
     * @param Item $item
     * @param $resourceKey
     * @return \Spatie\Fractal\Fractal
     */
    public static function transformItem(Item $item, $resourceKey = null)
    {
        return fractal()
            ->item($item, new ItemTransformer())
            ->serializeWith(new ArraySerializer())
            ->withResourceName($resourceKey);
    }
    /**
     * @param Collection $items
     * @param $resourceKey
     * @return \Spatie\Fractal\Fractal
     */
    public static function transformCollection(Collection $items, $resourceKey = null)
    {
        return fractal()
            ->collection($items, new ItemTransformer())
            ->serializeWith(new ArraySerializer())
            ->withResourceName($resourceKey);
    }
}