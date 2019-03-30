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

    protected $admin;
    protected $store;

    protected function setUp()
    {
        parent::setUp();
        $this->store = factory(Store::class)->create();
        $this->admin = factory(User::class)->create([
            'api_token' => str_random(30),
            'role' => UserRole::Admin,
        ]);
    }

    /**
     * 13. POST /api/v1/store/{store}/users
     *
     * @test
     * @throws \Exception
     */
    public function AddStoreUser_DataCorrect_Success()
    {
        $response = $this->json('POST', 'api/v1/store/' . $this->store->id . '/users', [
            'api_token' => $this->admin->api_token,
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]);

        $createdStoreUser = User::where('email', self::EMAIL)->first();
        $this->assertEquals($createdStoreUser->email, self::EMAIL);
        $this->assertTrue(Hash::check(self::PASSWORD, $createdStoreUser->password));
        $this->assertEquals($createdStoreUser->role, UserRole::StoreUser);
        $this->assertEquals(StoreUser::where('user_id', $createdStoreUser->id)->first()->store_id, $this->store->id);
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
        $user = factory(User::class)->create([
            'email' => self::EMAIL,
            'password' => Hash::make(self::PASSWORD),
        ]);
        $response = $this->json('POST', 'api/v1/store/' . $this->store->id . '/users', [
            'api_token' => $this->admin->api_token,
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
        $storeUser = StoreUser::create([
            'store_id' => $this->store->id,
            'user_id' => factory(User::class)->create([
                "role" => UserRole::StoreUser,
            ])->id,
        ]);
        $response = $this->json('DELETE', 'api/v1/store/' . $this->store->id . '/users/' . $storeUser->user_id, [
            'api_token' => $this->admin->api_token,
        ]);

        $this->assertFalse(User::where('id', $storeUser->user_id)->exists());
        $this->assertFalse(StoreUser::where('store_id', $this->store->id)->where('user_id', $storeUser->user_id)->exists());
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
        $user = factory(User::class)->create();
        $response = $this->json('DELETE', 'api/v1/store/' . $this->store->id . '/users/' . $user->id, [
            'api_token' => $this->admin->api_token,
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
        $user = factory(User::class)->create([
            "role" => UserRole::StoreUser,
        ]);
        $response = $this->json('DELETE', 'api/v1/store/' . $this->store->id . '/users/' . $user->id, [
            'api_token' => $this->admin->api_token,
        ]);
        $response->assertStatus(404);
    }
}
