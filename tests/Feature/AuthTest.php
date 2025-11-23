<?php

namespace Tests\Feature;

use App\Modules\Auth\Models\LoginCode;
use App\Modules\Users\Models\Users;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_request_login_code(): void
    {
        $email = 'test@example.com';

        $response = $this->postJson('/api/auth/request-code', [
            'email' => $email,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Código enviado para seu e-mail',
            ]);

        $this->assertDatabaseHas('users', ['email' => $email]);
        $this->assertDatabaseHas('login_codes', [
            'user_id' => Users::where('email', $email)->first()->id,
        ]);
    }

    public function test_user_can_login_with_valid_code_and_get_token(): void
    {
        $user = Users::factory()->create(['email' => 'test@example.com']);
        $loginCode = LoginCode::factory()->create([
            'user_id' => $user->id,
            'code' => '123456',
            'expires_at' => now()->addMinutes(10),
            'used_at' => null,
        ]);

        $response = $this->postJson('/api/auth/verify-code', [
            'email' => $user->email,
            'code' => '123456',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'user' => ['id', 'name', 'email'],
                'token',
            ]);

        $this->assertNotNull($loginCode->fresh()->used_at);
    }

    public function test_cannot_login_with_invalid_or_expired_code(): void
    {
        $user = Users::factory()->create(['email' => 'test@example.com']);

        // Test with invalid code
        $response = $this->postJson('/api/auth/verify-code', [
            'email' => $user->email,
            'code' => '000000',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'Código inválido ou expirado',
            ]);

        // Test with expired code
        $loginCode = LoginCode::factory()->create([
            'user_id' => $user->id,
            'code' => '123456',
            'expires_at' => now()->subMinutes(1),
            'used_at' => null,
        ]);

        $response = $this->postJson('/api/auth/verify-code', [
            'email' => $user->email,
            'code' => '123456',
        ]);

        $response->assertStatus(422);
    }
}
