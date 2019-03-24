<?php

namespace App\Http\Transformers\V1;

use App\Models\User;
use Illuminate\Support\Collection;
use League\Fractal\TransformerAbstract;
use Spatie\Fractalistic\ArraySerializer;

class UserTransformer extends TransformerAbstract
{
    /**
     * @param User $user
     * @return array
     */
    public function transform(User $user)
    {
        return [
            'id' => (int) $user->id,
            'email' => $user->email,
            'created_at' => $user->created_at->toDateTimeString(),
        ];
    }
    /**
     * @param User $user
     * @param $resourceKey
     * @return \Spatie\Fractal\Fractal
     */
    public static function transformItem(User $user, $resourceKey = null)
    {
        return fractal()
            ->item($user, new UserTransformer())
            ->serializeWith(new ArraySerializer())
            ->withResourceName($resourceKey);
    }
    /**
     * @param Collection $users
     * @param $resourceKey
     * @return \Spatie\Fractal\Fractal
     */
    public static function transformCollection(Collection $users, $resourceKey = null)
    {
        return fractal()
            ->collection($users, new UserTransformer())
            ->serializeWith(new ArraySerializer())
            ->withResourceName($resourceKey);
    }
}