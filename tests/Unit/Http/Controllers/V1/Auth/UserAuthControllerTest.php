<?php

namespace Tests\Unit\Http\Controllers\V1\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserAuthControllerTest extends TestCase
{
    use RefreshDatabase;

    const EMAIL_SETUP = 'someemail@email.com';
    const PASSWORD_SETUP = 'qwerty123';
    const EMAIL_FOR_REGISTRATION = 'someemailRegistration@email.com';
    const PASSWORD_DIFFERENT = 'qwerty1234';

    protected $user;

    protected function setUp()
    {
        parent::setUp();
        $this->user = factory(User::class)->create([
            'email' => self::EMAIL_SETUP,
            'password' => Hash::make(self::PASSWORD_SETUP),
        ]);
    }

    /**
     * 1. GET api/v1/auth/register
     *
     * @test
     * @throws \Exception
     */
    public function Register_DataCorrect_Success()
    {
        $response = $this->json('GET', 'api/v1/register', [
            'email' => self::EMAIL_FOR_REGISTRATION,
            'password' => self::PASSWORD_SETUP,
        ]);

        $response->assertStatus(200);
        $response->assertJson(["success" => true]);
        $response->assertJsonFragment([
            "api_token" => User::where('email', self::EMAIL_FOR_REGISTRATION)->first()->api_token,
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
        $response = $this->json('GET', 'api/v1/register', [
            'email' => self::EMAIL_SETUP,
            'password' => self::PASSWORD_SETUP,
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

        // $user = factory(User::class)->create([
        //     'email' => self::EMAIL,
        //     'password' => Hash::make(self::PASSWORD),
        // ]);
        $response = $this->json('GET', 'api/v1/login', [
            'email' => self::EMAIL_SETUP,
            'password' => self::PASSWORD_SETUP,
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

        // $user = factory(User::class)->create([
        //     'email' => self::EMAIL,
        //     'password' => Hash::make(self::PASSWORD),
        // ]);
        $response = $this->json('GET', 'api/v1/login', [
            'email' => self::EMAIL_SETUP,
            'password' => self::PASSWORD_DIFFERENT,
        ]);

        $response->assertStatus(401);
    }
}
