<?php

namespace App\Http\Controllers\V1\Auth;

use Socialite;
use App\Http\Controllers\Controller;
use App\Models\User;

class GitHubAuthController extends Controller
{
    /**
     * Redirect the user to the GitHub authentication page.
     *
     * @return \Illuminate\Http\Response
     */
    public function redirectToProvider()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Obtain the user information from GitHub.
     *
     * @return \Illuminate\Http\Response
     */
    public function handleProviderCallback()
    {
        $user = Socialite::driver('github')->user();
        $authUser = User::firstOrNew([
            'email' => $user->getEmail()
        ]);
        $authUser->full_name = $user->getNickname();
        $authUser->rollApiKey();
        $authUser->save();
        return response()->json([
            "success" => true,
            "data" => [
                "api_token" => $authUser->api_token,
            ],
        ]);
    }
}