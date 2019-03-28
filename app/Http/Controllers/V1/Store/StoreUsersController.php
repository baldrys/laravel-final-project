<?php

namespace App\Http\Controllers\V1\Store;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\EmailPasswordRequest;
use App\Http\Transformers\V1\UserTransformer;
use App\Models\Store;
use App\Models\StoreUser;
use App\Models\User;
use App\Support\Enums\UserRole;
use Hash;
use Illuminate\Http\Request;

class StoreUsersController extends Controller
{
    /**
     * @param Store $store
     * @param EmailPasswordRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addStoreUser(Store $store, EmailPasswordRequest $request)
    {

        $user = factory(User::class)->create([
            "full_name" => $request->full_name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
            "role" => UserRole::StoreUser,
        ]);
        StoreUser::create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);
        return response()->json([
            "success" => true,
            "data" => [
                "created_store_user" => UserTransformer::transformItem($user),
            ],
        ]);
    }

    /**
     * @param Store $store
     * @param User $user
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteStoreUser(Store $store, User $user, Request $request)
    {
        if ($user->role != UserRole::StoreUser) {
            return response()->json([
                "success" => false,
                "message" => "User " . $user->id . " не является userStore",
            ], 400);
        }

        $storeUser = StoreUser::where('user_id', $user->id)->where('store_id', $store->id);
        if (!$storeUser->exists()) {
            return response()->json([
                "success" => false,
                "message" => "User " . $user->id . " не является сотрудником store " . $store->id,
            ], 404);
        }

        $user->delete();
        return response()->json([
            "success" => true,
        ]);

    }
}
