<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_login_with_valid_credentials()
    {
        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'role' => 'student',
            'is_active' => true,
        ]);

        // Attempt login
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        // Assert successful login
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect();
    }

    /** @test */
    public function user_cannot_login_with_invalid_credentials()
    {
        // Create a test user
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'is_active' => true,
        ]);

        // Attempt login with wrong password
        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        // Assert failed login
        $this->assertGuest();
    }

    /** @test */
    public function authenticated_student_can_access_dashboard()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'role' => 'student',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/student/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_student_dashboard()
    {
        $response = $this->get('/student/dashboard');

        $response->assertRedirect('/login');
    }

    /** @test */
    public function admin_can_access_admin_dashboard()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function teacher_can_access_teacher_dashboard()
    {
        /** @var User $user */
        $user = User::factory()->create([
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get('/teacher/dashboard');

        $response->assertStatus(200);
    }
}
