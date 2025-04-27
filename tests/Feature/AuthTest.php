<?php

namespace Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function testUserCanLoginAndReceiveJwtToken(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Act
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'user' => ['id', 'name', 'email'],
                'authorisation' => [
                    'token', 'type',
                ],
            ])
            ->assertJson([
                'status' => 'success',
                'authorisation' => [
                    'type' => 'bearer',
                ],
            ]);
    }

    public function testUserCanRegisterSuccessfully(): void
    {
        // Act
        $response = $this->postJson('/api/v1/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'message',
                'user' => [
                    'id', 'name', 'email',
                ],
                'authorisation' => [
                    'token', 'type',
                ],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }

    public function testUserCanLogoutSuccessfully(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // Act
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/logout');

        // Assert
        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'message' => 'Successfully logged out',
            ]);

        $this->assertTrue(Auth::guest());
    }

    public function testAuthenticatedUserCanRefreshTokenSuccessfully(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = JWTAuth::fromUser($user);

        // Act
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/refresh');

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'user' => [
                    'id', 'name', 'email', 'email_verified_at'
                ],
                'authorisation' => [
                    'token', 'type',
                ],
            ]);

        $this->assertSame('success', $response['status']);
        $this->assertSame('bearer', $response['authorisation']['type']);
    }

    public function testLoginFailsWithInvalidCredentials(): void
    {
        // Arrange
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct-password'),
        ]);

        // Act
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'status' => 'error',
                'message' => 'Incorrect email or password.',
            ]);
    }

    public function testLoginFailsWithInvalidEmail(): void
    {
        // Act
        $response = $this->postJson('/api/v1/login', [
            'email' => 'not-an-email',
            'password' => 'short',
        ]);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    public function testLoginValidationErrorsIfNoDataIsSent(): void
    {
        // Act
        $response = $this->postJson('/api/v1/login', []);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email', 'password']);
    }

    public function testUserCannotLogoutWithoutAuthentication(): void
    {
        // Act
        $response = $this->postJson('/api/v1/logout');

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }

    public function testUnauthenticatedUserCannotRefreshToken(): void
    {
        // Act
        $response = $this->postJson('/api/v1/refresh');

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.',
            ]);
    }
}
