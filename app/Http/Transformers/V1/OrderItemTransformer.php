<?php

namespace App\Http\Transformers\V1;

use App\Models\OrderItem;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use Spatie\Fractalistic\ArraySerializer;

class OrderItemTransformer extends TransformerAbstract
{
    /**
     * @param OrderItem $orderItem
     * @return array
     */
    public function transform(OrderItem $orderItem)
    {
        return [
            'item_id' => (int) $orderItem->item_id,
            'amount' => (int) $orderItem->amount,
        ];
    }
    /**
     * @param OrderItem $orderItem
     * @param $resourceKey
     * @return \Spatie\Fractal\Fractal
     */
    public static function transformItem(OrderItem $orderItem, $resourceKey = null)
    {
        return fractal()
            ->item($orderItem, new CartItemTransformer())
            ->serializeWith(new ArraySerializer())
            ->withResourceName($resourceKey);
    }
    /**
     * @param Collection $orderItems
     * @param $resourceKey
     * @return \Spatie\Fractal\Fractal
     */
    public static function transformCollection(Collection $orderItems, $resourceKey = null)
    {
        return fractal()
            ->collection($orderItems, new OrderItemTransformer())
            ->serializeWith(new ArraySerializer())
            ->withResourceName($resourceKey);
    }
}