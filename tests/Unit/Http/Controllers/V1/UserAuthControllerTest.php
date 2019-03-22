<?php

namespace Tests\Unit\Http\Controllers\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    const EMAIL = 'someemail@email.com';
    const PASSWORD = 'qwerty123';
    const PASSWORD_FAIL = 'qwerty1234';
    
    /**
     * 1. GET api/v1/auth/register
     *
     * @test
     * @throws \Exception
     */
    public function Register_DataCorrect_Success()
    {
        $response = $this->json('GET', 'api/v1/register', [
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]);

        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
        $response->assertJsonFragment([
            "api_token" => User::where('email', self::EMAIL)->first()->api_token,
        ]);
    }

    /**
     * 1. GET api/v1/auth/register
     *
     * @test
     * @throws \Exception
     */
    public function Register_EmailAlreadyExists_Failed()
    {

        $user = factory(User::class)->create([
            'email' => self::EMAIL,
            'password' => Hash::make(self::PASSWORD),
        ]);
        $response = $this->json('GET', 'api/v1/register', [
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]);

        $response->assertStatus(400);
    }

    /**
     * 2. GET api/v1/auth/login
     *
     * @test
     * @throws \Exception
     */
    public function Login_Datacorrect_Success()
    {

        $user = factory(User::class)->create([
            'email' => self::EMAIL,
            'password' => Hash::make(self::PASSWORD),
        ]);
        $response = $this->json('GET', 'api/v1/login', [
            'email' => self::EMAIL,
            'password' => self::PASSWORD,
        ]);

        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
    }

    /**
     * 2. GET api/v1/auth/login
     *
     * @test
     * @throws \Exception
     */
    public function Login_DataIncorrect_Unauthorized()
    {

        $user = factory(User::class)->create([
            'email' => self::EMAIL,
            'password' => Hash::make(self::PASSWORD),
        ]);
        $response = $this->json('GET', 'api/v1/login', [
            'email' => self::EMAIL,
            'password' => self::PASSWORD_FAIL,
        ]);

        $response->assertStatus(401);
    }
}
