<?php

namespace Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function testSuccessfulPasswordReset(): void
    {
       // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);
        $token = Password::createToken($user);

        // Act
        $response = $this->actingAs($user, 'api')->postJson('/api/v1/password/reset', [
            'email' => 'test@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => $token,
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Password has been reset',
            ]);
    }

    public function testInvalidTokenInResetPassword():void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Act
        $response = $this->actingAs($user, 'api')->postJson('/api/v1/password/reset', [
            'email' => 'test@example.com',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
            'token' => 'invalidtoken',
        ]);

        // Assert
        $response->assertStatus(400)
            ->assertJson([
                'error' => 'Failed to reset password',
            ]);
    }

    public function testMissingPasswordWhenTryingToDoAResetPassword(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Act
        $response = $this->actingAs($user, 'api')->postJson('/api/v1/password/reset', [
            'email' => 'test@example.com',
            'token' => 'validtoken',
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The password field is required.',
                'errors' => [
                    'password' => ['The password field is required.'],
                ]
            ]);
    }
}

