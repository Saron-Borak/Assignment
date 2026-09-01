<?php

namespace Tests\Feature;

use App\Models\Lecturer;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\SeedsAttendance;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase, SeedsAttendance;

    public function test_the_login_screen_is_reachable(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('East Asia Management University');
    }

    public function test_an_administrator_lands_on_the_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create(['email' => 'admin@eamu.edu']);

        $this->post('/login', ['email' => 'admin@eamu.edu', 'password' => 'password'])
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_a_lecturer_lands_on_the_lecturer_dashboard(): void
    {
        $lecturer = $this->makeLecturer('teach@eamu.edu');

        $this->post('/login', ['email' => 'teach@eamu.edu', 'password' => 'password'])
            ->assertRedirect(route('lecturer.dashboard'));

        $this->assertAuthenticatedAs($lecturer->user);
    }

    public function test_a_student_lands_on_the_student_dashboard(): void
    {
        $student = $this->makeStudent('learn@student.eamu.edu');

        $this->post('/login', ['email' => 'learn@student.eamu.edu', 'password' => 'password'])
            ->assertRedirect(route('student.dashboard'));

        $this->assertAuthenticatedAs($student->user);
    }

    public function test_the_wrong_password_is_rejected(): void
    {
        User::factory()->admin()->create(['email' => 'admin@eamu.edu']);

        $this->post('/login', ['email' => 'admin@eamu.edu', 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_deactivated_account_cannot_sign_in_even_with_the_right_password(): void
    {
        User::factory()->admin()->inactive()->create(['email' => 'former@eamu.edu']);

        $this->post('/login', ['email' => 'former@eamu.edu', 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_an_account_deactivated_mid_session_is_signed_out_on_its_next_request(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $admin->update(['is_active' => false]);

        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guests_are_redirected_to_the_login_screen(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_signing_out_ends_the_session(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
