<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create basic roles for testing
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'college_user']);
        Role::create(['name' => 'area_user']);
    }

    /** @test */
    public function it_can_create_a_user_with_required_fields()
    {
        $user = User::create([
            'employee_id' => 'EMP001',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'password' => 'password123',
            'role' => 'admin',
            'status' => 'active',
            'theme_mode' => 'light',
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('EMP001', $user->employee_id);
        $this->assertEquals('John', $user->first_name);
        $this->assertEquals('Doe', $user->last_name);
        $this->assertEquals('john.doe@example.com', $user->email);
        $this->assertEquals('admin', $user->role);
        $this->assertEquals('active', $user->status);
    }

    /** @test */
    public function it_hides_sensitive_attributes()
    {
        $user = User::factory()->create([
            'password' => 'secret123',
            'two_factor_secret' => 'secret_key',
            'two_factor_recovery_codes' => json_encode(['code1', 'code2']),
        ]);

        $userArray = $user->toArray();

        $this->assertArrayNotHasKey('password', $userArray);
        $this->assertArrayNotHasKey('remember_token', $userArray);
        $this->assertArrayNotHasKey('two_factor_secret', $userArray);
        $this->assertArrayNotHasKey('two_factor_recovery_codes', $userArray);
    }

    /** @test */
    public function it_casts_attributes_correctly()
    {
        $user = User::factory()->create([
            'permissions' => ['read', 'write'],
            'two_factor_enabled' => true,
            'theme_preferences' => ['color' => 'blue'],
        ]);

        $this->assertIsArray($user->permissions);
        $this->assertIsBool($user->two_factor_enabled);
        $this->assertIsArray($user->theme_preferences);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->created_at);
    }

    /** @test */
    public function it_returns_full_name_attribute()
    {
        $user = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
        ]);

        $this->assertEquals('John Doe', $user->name);
    }

    /** @test */
    public function it_returns_unknown_user_when_name_is_empty()
    {
        $user = User::factory()->create([
            'first_name' => '',
            'last_name' => '',
        ]);

        $this->assertEquals('Unknown User', $user->name);
    }

    /** @test */
    public function it_returns_default_theme_mode_when_not_set()
    {
        $user = User::factory()->create(['theme_mode' => null]);

        $this->assertEquals('light', $user->getThemeMode());
    }

    /** @test */
    public function it_returns_user_theme_mode_when_set()
    {
        $user = User::factory()->create(['theme_mode' => 'dark']);

        $this->assertEquals('dark', $user->getThemeMode());
    }

    /** @test */
    public function it_can_set_theme_mode()
    {
        $user = User::factory()->create();

        $user->setThemeMode('dark');

        $this->assertEquals('dark', $user->fresh()->theme_mode);
    }

    /** @test */
    public function it_returns_default_theme_preferences()
    {
        $user = User::factory()->create(['theme_preferences' => null]);

        $preferences = $user->getThemePreferences();

        $this->assertIsArray($preferences);
        $this->assertEquals('#800000', $preferences['primary_color']);
        $this->assertEquals('default', $preferences['sidebar_style']);
        $this->assertEquals('medium', $preferences['font_size']);
    }

    /** @test */
    public function it_merges_user_theme_preferences_with_defaults()
    {
        $user = User::factory()->create([
            'theme_preferences' => ['primary_color' => '#ff0000']
        ]);

        $preferences = $user->getThemePreferences();

        $this->assertEquals('#ff0000', $preferences['primary_color']);
        $this->assertEquals('default', $preferences['sidebar_style']);
        $this->assertEquals('medium', $preferences['font_size']);
    }

    /** @test */
    public function it_can_update_theme_preferences()
    {
        $user = User::factory()->create([
            'theme_preferences' => ['primary_color' => '#800000']
        ]);

        $user->updateThemePreferences(['sidebar_style' => 'compact']);

        $updated = $user->fresh()->theme_preferences;
        $this->assertEquals('#800000', $updated['primary_color']);
        $this->assertEquals('compact', $updated['sidebar_style']);
    }

    /** @test */
    public function it_has_roles_functionality()
    {
        $user = User::factory()->create();
        $role = Role::findByName('admin');

        $user->assignRole($role);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertContains('admin', $user->getRoleNames()->toArray());
    }

    /** @test */
    public function it_can_assign_multiple_roles()
    {
        $user = User::factory()->create();

        $user->assignRole(['admin', 'college_user']);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertTrue($user->hasRole('college_user'));
        $this->assertCount(2, $user->roles);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        User::create([
            'first_name' => 'John',
            // Missing required email field
        ]);
    }

    /** @test */
    public function it_ensures_unique_email()
    {
        User::factory()->create(['email' => 'test@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);

        User::factory()->create(['email' => 'test@example.com']);
    }

    /** @test */
    public function it_hashes_password_automatically()
    {
        $user = User::factory()->create(['password' => 'plaintext']);

        $this->assertNotEquals('plaintext', $user->getAuthPassword());
        $this->assertTrue(\Hash::check('plaintext', $user->getAuthPassword()));
    }
}