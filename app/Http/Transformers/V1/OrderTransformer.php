<?php

namespace App\Http\Transformers\V1;

use App\Models\Order;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use Spatie\Fractalistic\ArraySerializer;

class OrderTransformer extends TransformerAbstract
{
    /**
     * @param Order $user
     * @return array
     */
    public function transform(Order $order)
    {
        $orderItems = $order->orderItems()->get();
        return [
            'id' => (int) $order->id,
            'user_id' => $order->customer_id,
            'store_id' => $order->store_id,
            'total_price' => $order->total_price,
            'items' => OrderItemTransformer::transformCollection($orderItems),

        ];
    }
    /**
     * @param User $user
     * @param $resourceKey
     * @return \Spatie\Fractal\Fractal
     */
    public static function transformItem(Order $order, $resourceKey = null)
    {
        return fractal()
            ->item($order, new OrderTransformer())
            ->serializeWith(new ArraySerializer())
            ->withResourceName($resourceKey);
    }
    /**
     * @param Collection $users
     * @param $resourceKey
     * @return \Spatie\Fractal\Fractal
     */
    public static function transformCollection(Collection $orders, $resourceKey = null)
    {
        return fractal()
            ->collection($orders, new OrderTransformer())
            ->serializeWith(new ArraySerializer())
            ->withResourceName($resourceKey);
    }
}