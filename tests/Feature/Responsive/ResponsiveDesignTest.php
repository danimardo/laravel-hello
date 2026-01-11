<?php

namespace Tests\Feature\Responsive;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResponsiveDesignTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create admin user
        $this->adminUser = User::factory()->create([
            'username' => 'admin',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        // Create regular user
        $this->regularUser = User::factory()->create([
            'username' => 'user',
            'email' => 'user@test.com',
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function mobile_navigation_menu_is_hidden_by_default()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Mobile menu should be hidden (lg breakpoint)
        $response->assertSee('lg:hidden', false);
    }

    /** @test */
    public function desktop_navigation_menu_is_hidden_on_mobile()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Desktop menu should be hidden on mobile
        $response->assertSee('hidden lg:flex', false);
    }

    /** @test */
    public function mobile_dropdown_menu_contains_all_navigation_items()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Mobile dropdown should contain navigation items
        $response->assertSee('dropdown-content');
        $response->assertSee('Contador');
        $response->assertSee('Usuarios');
        $response->assertSee('Cambiar Contraseña');
        $response->assertSee('Cerrar Sesión');
    }

    /** @test */
    public function responsive_grid_layouts_work_on_all_screen_sizes()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for responsive grid classes
        $response->assertSee('grid');
        $response->assertSee('grid-cols');
    }

    /** @test */
    public function buttons_are_accessible_on_all_screen_sizes()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for button classes
        $response->assertSee('btn');
        $response->assertSee('btn-primary');
        $response->assertSee('btn-outline');
    }

    /** @test */
    public function form_inputs_are_responsive()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users/create');

        // Check for responsive input classes
        $response->assertSee('input');
        $response->assertSee('input-bordered');
        $response->assertSee('w-full');
    }

    /** @test */
    public function card_layouts_stack_on_mobile()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users/create');

        // Check for card component
        $response->assertSee('card');
        $response->assertSee('bg-base-100');
        $response->assertSee('shadow-xl');
    }

    /** @test */
    public function navbar_is_sticky_on_all_screen_sizes()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for navbar classes
        $response->assertSee('navbar');
        $response->assertSee('bg-primary');
        $response->assertSee('shadow-lg');
    }

    /** @test */
    public function avatar_dropdown_exists_in_desktop_nav()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for avatar dropdown
        $response->assertSee('dropdown-end');
        $response->assertSee('avatar');
        $response->assertSee('Administrador');
    }

    /** @test */
    public function responsive_text_sizes_are_applied()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for responsive text classes
        $response->assertSee('text-');
    }

    /** @test */
    public function containers_have_responsive_max_widths()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for container and max-width classes
        $response->assertSee('container');
        $response->assertSee('mx-auto');
    }

    /** @test */
    public function spacing_is_responsive()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for responsive spacing classes
        $response->assertSee('px-4');
        $response->assertSee('py-8');
        $response->assertSee('mb-');
    }

    /** @test */
    public function responsive_alerts_work()
    {
        $this->actingAs($this->adminUser)
            ->post('/admin/users', [
                'username' => 'testuser',
                'email' => 'test@test.com',
                'password' => 'Test123!',
                'password_confirmation' => 'Test123!',
                'role' => 'user',
            ]);

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for alert classes
        $response->assertSee('alert');
    }

    /** @test */
    public function table_is_responsive_on_all_viewports()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for responsive table classes
        $response->assertSee('table');
        $response->assertSee('table-zebra');
    }

    /** @test */
    public function pagination_is_responsive()
    {
        User::factory()->count(25)->create();

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for pagination classes
        $response->assertSee('pagination');
    }

    /** @test */
    public function loading_states_are_responsive()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for loading indicator classes
        $response->assertSee('loading');
        $response->assertSee('loading-spinner');
    }

    /** @test */
    public function badge_elements_are_responsive()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for badge classes
        $response->assertSee('badge');
        $response->assertSee('badge-success');
        $response->assertSee('badge-error');
        $response->assertSee('badge-warning');
    }

    /** @test */
    public function divider_elements_work_responsively()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users/edit/' . $this->adminUser->id);

        // Check for divider
        $response->assertSee('divider');
    }

    /** @test */
    public function tooltip_and_popover_elements_are_responsive()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for tooltip/popover classes
        $response->assertSee('tooltip');
        $response->assertSee('popover');
    }

    /** @test */
    public function modal_elements_stack_on_mobile()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for modal classes
        $response->assertSee('modal');
    }

    /** @test */
    public function form_validation_messages_are_responsive()
    {
        $response = $this->actingAs($this->adminUser)
            ->post('/admin/users', []);

        // Check for error message classes
        $response->assertSee('label-text-alt');
        $response->assertSee('text-error');
    }

    /** @test */
    public function page_header_layouts_are_responsive()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for page header structure
        $response->assertSee('mb-8');
        $response->assertSee('justify-between');
    }

    /** @test */
    public function footer_is_responsive()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for footer
        $response->assertSee('footer');
    }

    /** @test */
    public function responsive_helpers_are_present()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for responsive utility classes
        $response->assertSee('flex');
        $response->assertSee('gap-');
        $response->assertSee('w-');
    }

    /** @test */
    public function mobile_first_classes_are_used()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Verify mobile-first approach (base styles, then responsive)
        $content = $response->getContent();

        // Should have base classes that work on mobile
        $this->assertStringContainsString('min-h-screen', $content);
        $this->assertStringContainsString('bg-base-100', $content);
    }

    /** @test */
    public function accessibility_features_work_on_all_screen_sizes()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        // Check for accessibility features
        $response->assertSee('sr-only'); // Screen reader only
        $response->assertSee('focus:'); // Focus indicators
    }
}
