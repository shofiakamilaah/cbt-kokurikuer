<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\User;
use Database\Seeders\UserSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_registration_creates_student_account_and_redirects_to_login(): void
    {
        $classroom = Classroom::create([
            'name' => 'PPLG X-1',
            'grade_level' => 10,
        ]);

        $response = $this->post(route('register.store'), [
            'name' => 'Siswa Revisi',
            'nis' => '12500001',
            'classroom_id' => $classroom->id,
            'email' => 'siswa.revisi@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        $this->assertGuest();

        $user = User::where('email', 'siswa.revisi@example.test')->firstOrFail();

        $this->assertSame('student', $user->role);
        $this->assertSame(10, $user->classroom->grade_level);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    public function test_user_seeder_creates_only_teacher_and_student_roles(): void
    {
        $this->seed(UserSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'guru@example.com',
            'role' => 'teacher',
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'siswa@example.com',
            'role' => 'student',
        ]);
        $this->assertDatabaseMissing('users', [
            'role' => 'admin',
        ]);
    }

    public function test_seeded_teacher_can_login_and_is_redirected_to_dashboard(): void
    {
        $this->seed(UserSeeder::class);

        $response = $this->post(route('login.store'), [
            'email' => 'guru@example.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_user_seeder_preserves_existing_admin_accounts(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password123',
            'role' => 'admin',
        ]);

        $this->seed(UserSeeder::class);

        $this->assertModelExists($admin);
    }
}
