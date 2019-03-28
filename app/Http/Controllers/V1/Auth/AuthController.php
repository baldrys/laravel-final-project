<?php

namespace App\Http\Controllers\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\V1\EmailPasswordRequest;
use App\Models\User;
use Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    /**
     * @param EmailPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(EmailPasswordRequest $request)
    {
        $user = User::where("email", $request->email)->first();
        if (!$user) {
            abort(404, "Пользователь не существует");
        }
        if (!Hash::check($request->password, $user->password)) {
            abort(401, "Неверный логин / пароль");
        }
        $user->rollApiKey();
        return response()->json([
            "success" => true,
            "data" => [
                "api_token" => $user->api_token,
            ],
        ]);
    }

    /**
     * @param EmailPasswordRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(EmailPasswordRequest $request)
    {
        $user = User::where("email", $request->email)->first();
        if ($user) {
            abort(400, "Пользователь c таким email уже зарегистрирован!");
        }
        $user = factory(User::class)->create([
            "full_name" => $request->full_name,
            "email" => $request->email,
            "password" => Hash::make($request->password),
        ]);
        $user->rollApiKey();
        return response()->json([
            "success" => true,
            "data" => [
                "api_token" => $user->api_token,
            ],
        ]);
    }
}
