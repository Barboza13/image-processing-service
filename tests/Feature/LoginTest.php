<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    /**
     * Configure the tests environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            "name" => "proof_name",
            "email" => "proof_email@email.com",
            "password" => "proof_password",
        ]);
    }

    /**
     * Test register user.
     *
     * @return void
     */
    public function test_register_user(): void
    {
        $response = $this->postJson("api/register", [
            "name" => "proof_name2",
            "email" => "proof_email2@email.com",
            "password" => "proof_password2",
            "password_confirmation" => "proof_password2",
        ]);

        $response
            ->assertStatus(201)
            ->assertJson([
                "user" => true,
                "message" => true
            ]);
    }

    /**
     * Test login with valid credentials.
     *
     * @return void
     */
    public function test_login_with_valid_credentials(): void
    {
        $response = $this->postJson("api/login", [
            "email" => "proof_email@email.com",
            "password" => "proof_password"
        ]);

        $response
            ->assertStatus(200)
            ->assertJson([
                "user" => true,
                "token" => true
            ]);
    }

    /**
     * Test login with invalid credentials.
     *
     * @return void
     */
    public function test_login_with_invalid_credentials(): void
    {
        $response = $this->postJson("api/login", [
            "email" => "proof_email@email.com",
            "password" => "invalid_password"
        ]);

        $response
            ->assertStatus(401)
            ->assertJson([
                "message" => true,
            ]);
    }

    /**
     * Test successfully logout.
     *
     * @return void
     */
    public function test_logout_user(): void
    {
        $response = $this->postJson("api/login", [
            "email" => "proof_email@email.com",
            "password" => "proof_password"
        ]);
        $token = $response["token"];

        $response = $this->withHeader("Authorization", "Bearer $token")->postJson("api/logout");

        $response
            ->assertStatus(200)
            ->assertJson([
                "message" => true
            ]);


        // Check if token still exists.
        $db_token = DB::table("personal_access_tokens")->select("token")->get();
        $this->assertEmpty($db_token);
    }

    /**
     * Test logout without token.
     *
     * @return void
     */
    public function test_logout_without_token(): void
    {
        $response = $this->postJson("api/logout");
        $response->assertStatus(401);
    }
}
