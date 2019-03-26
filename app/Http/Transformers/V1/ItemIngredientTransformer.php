<?php

namespace App\Http\Transformers\V1;

use App\Models\Ingredient;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use Spatie\Fractalistic\ArraySerializer;
use App\Models\ItemIngredients;

class ItemIngredientTransformer extends TransformerAbstract
{
    /**
     * @param Item $item
     * @return array
     */
    public function transform(ItemIngredients $itemIngredient)
    {
        $ingredient = $itemIngredient->itemIngredient;

        return [
            'id' => (int) $ingredient->id,
            'store_id' => $ingredient->store_id,
            'name' => $ingredient->name,
            'price' => $ingredient->price,
            'amount' => $itemIngredient->amount,

        ];
    }
    /**
     * @param Item $item
     * @param $resourceKey
     * @return \Spatie\Fractal\Fractal
     */
    public static function transformItem(ItemIngredients $itemIngredient, $resourceKey = null)
    {
        return fractal()
            ->item($itemIngredient, new ItemIngredientTransformer())
            ->serializeWith(new ArraySerializer())
            ->withResourceName($resourceKey);
    }
    /**
     * @param Collection $items
     * @param $resourceKey
     * @return \Spatie\Fractal\Fractal
     */
    public static function transformCollection(Collection $itemIngredients, $resourceKey = null)
    {
        return fractal()
            ->collection($itemIngredients, new ItemIngredientTransformer())
            ->serializeWith(new ArraySerializer())
            ->withResourceName($resourceKey);
    }
}