<?php

namespace Tests\Unit\Http\Controllers\V1;

use App\Models\Store;
use App\Models\StoreUser;
use App\Models\User;
use App\Support\Enums\UserRole;
use Hash;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreUsersControllerTest extends TestCase
{
    use RefreshDatabase;

    const EMAIL = 'someemail@email.com';
    const PASSWORD = 'qwerty123';
    const PASSWORD_FAIL = 'qwerty1234';

    /**
     * 13. POST /api/v1/store/{store}/users
     *
     * @test
     * @throws \Exception
     */
    public function AddStoreUser_DataCorrect_Success()
    {
        $store = factory(Store::class)->create();
        $admin = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::Admin,
        ]);

        $response = $this->json('POST', 'api/v1/store/' . $store->id . '/users', [
            'api_token' => $admin->api_token,
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]);

        $createdStoreUser = User::where('email', self::EMAIL)->first();
        $this->assertEquals($createdStoreUser->email, self::EMAIL);
        $this->assertTrue(Hash::check(self::PASSWORD, $createdStoreUser->password));
        $this->assertEquals($createdStoreUser->role, UserRole::StoreUser);
        $this->assertEquals(StoreUser::where('user_id', $createdStoreUser->id)->first()->store_id, $store->id);
        $response->assertStatus(201);
        $response->assertJson(["success" => true]);
    }

    /**
     * 13. POST /api/v1/store/{store}/users
     *
     * @test
     * @throws \Exception
     */
    public function AddStoreUser_EmailAlreadyExists_Failed()
    {
        $store = factory(Store::class)->create();
        $admin = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::Admin,
        ]);
        $user = factory(User::class)->create([
            'email' => self::EMAIL,
            'password' => Hash::make(self::PASSWORD),
        ]);
        $response = $this->json('POST', 'api/v1/store/' . $store->id . '/users', [
            'api_token' => $admin->api_token,
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]);

        $response->assertStatus(400);
    }

    /**
     * 14. DELETE /api/v1/store/{store}/users/{user}
     *
     * @test
     * @throws \Exception
     */
    public function DeleteStoreUser_DataCorrect_Success()
    {
        $store = factory(Store::class)->create();
        $admin = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::Admin,
        ]);

        $user = factory(User::class)->create([
            "role" => UserRole::StoreUser,
        ]);

        StoreUser::create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);
        $response = $this->json('DELETE', 'api/v1/store/' . $store->id . '/users/' . $user->id, [
            'api_token' => $admin->api_token,
        ]);

        $this->assertFalse(User::where('id', $user->id)->exists());
        $this->assertFalse(StoreUser::where('store_id', $store->id)->where('user_id', $user->id)->exists());
        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
    }

    /**
     * 14. DELETE /api/v1/store/{store}/users/{user}
     *
     * @test
     * @throws \Exception
     */
    public function DeleteStoreUser_NotUserStore_Fail()
    {
        $store = factory(Store::class)->create();
        $admin = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::Admin,
        ]);

        $user = factory(User::class)->create();

        StoreUser::create([
            'store_id' => $store->id,
            'user_id' => $user->id,
        ]);
        $response = $this->json('DELETE', 'api/v1/store/' . $store->id . '/users/' . $user->id, [
            'api_token' => $admin->api_token,
        ]);

        $response->assertStatus(400);
    }

    /**
     * 14. DELETE /api/v1/store/{store}/users/{user}
     *
     * @test
     * @throws \Exception
     */
    public function DeleteStoreUser_NotUserForStore_NotFound()
    {
        $store = factory(Store::class)->create();
        $admin = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::Admin,
        ]);

        $user = factory(User::class)->create([
            "role" => UserRole::StoreUser,
        ]);

        $response = $this->json('DELETE', 'api/v1/store/' . $store->id . '/users/' . $user->id, [
            'api_token' => $admin->api_token,
        ]);

        $response->assertStatus(404);
    }
}
