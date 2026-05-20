<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_be_created(): void
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    }

    public function test_user_has_required_fillable_attributes(): void
    {
        $user = new User();
        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    public function test_user_password_is_hidden(): void
    {
        $user = User::factory()->create();
        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
    }

    public function test_guest_is_redirected_from_protected_routes(): void
    {
        $response = $this->get('/app');
        $response->assertStatus(302);

        $response = $this->get('/admin');
        $response->assertStatus(302);
    }
}
