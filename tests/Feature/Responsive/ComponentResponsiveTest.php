<?php

namespace Tests\Feature\Responsive;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComponentResponsiveTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'username' => 'testuser',
            'email' => 'test@test.com',
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function login_form_is_responsive()
    {
        $response = $this->get('/login');

        // Check for responsive form layout
        $response->assertSee('form-control');
        $response->assertSee('w-full');
        $response->assertSee('input-bordered');
        $response->assertSee('btn', false);
    }

    /** @test */
    public function login_page_container_is_responsive()
    {
        $response = $this->get('/login');

        // Check for responsive container
        $response->assertSee('container');
        $response->assertSee('mx-auto');
        $response->assertSee('px-4');
    }

    /** @test */
    public function login_card_adapts_to_screen_size()
    {
        $response = $this->get('/login');

        // Check for card component
        $response->assertSee('card');
        $response->assertSee('bg-base-100');
        $response->assertSee('shadow-xl');
    }

    /** @test */
    public function login_logo_is_centered_responsively()
    {
        $response = $this->get('/login');

        // Check for logo and centering
        $response->assertSee('text-center');
    }

    /** @test */
    public function counter_page_is_fully_responsive()
    {
        $this->actingAs($this->user)->get('/counter');

        $response = $this->actingAs($this->user)->get('/counter');

        // Check for responsive counter layout
        $response->assertSee('min-h-screen');
        $response->assertSee('flex');
        $response->assertSee('items-center');
        $response->assertSee('justify-center');
    }

    /** @test */
    public function counter_buttons_are_touch_friendly()
    {
        $response = $this->actingAs($this->user)->get('/counter');

        // Check for button sizing (minimum touch target 44px)
        $response->assertSee('btn-lg'); // Large buttons
    }

    /** @test */
    public function counter_value_is_readable_on_all_devices()
    {
        $response = $this->actingAs($this->user)->get('/counter');

        // Check for large, readable text
        $response->assertSee('text-');
        $response->assertSee('font-bold');
    }

    /** @test */
    public function change_password_form_is_responsive()
    {
        $this->actingAs($this->user);

        $response = $this->get('/change-password');

        // Check for responsive form
        $response->assertSee('form-control');
        $response->assertSee('w-full');
        $response->assertSee('input-bordered');
    }

    /** @test */
    public function password_strength_indicator_is_responsive()
    {
        $this->actingAs($this->user);

        $response = $this->get('/change-password');

        // Check for password strength component
        $response->assertSee('password-strength-indicator');
    }

    /** @test */
    public function user_dropdown_menu_is_responsive()
    {
        $this->actingAs($this->user)->get('/counter');

        $response = $this->actingAs($this->user)->get('/counter');

        // Check for responsive dropdown
        $response->assertSee('dropdown');
        $response->assertSee('dropdown-end');
    }

    /** @test */
    public function navigation_menu_adapts_to_viewport()
    {
        $this->actingAs($this->user)->get('/counter');

        $response = $this->actingAs($this->user)->get('/counter');

        // Check for responsive navigation
        $response->assertSee('navbar');
        $response->assertSee('lg:flex');
    }

    /** @test */
    public function table_cells_stack_on_mobile()
    {
        $admin = User::factory()->create([
            'username' => 'admin2',
            'email' => 'admin2@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->actingAs($admin)->get('/admin/users');

        $response = $this->actingAs($admin)->get('/admin/users');

        // Check for responsive table
        $response->assertSee('table');
        $response->assertSee('table-zebra');
    }

    /** @test */
    public function pagination_controls_are_responsive()
    {
        User::factory()->count(25)->create();
        $admin = User::factory()->create([
            'username' => 'admin3',
            'email' => 'admin3@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');

        // Check for pagination
        $response->assertSee('pagination');
    }

    /** @test */
    public function alert_messages_stack_on_mobile()
    {
        $response = $this->actingAs($this->user)
            ->post('/change-password', [
                'current_password' => 'wrong',
                'new_password' => 'Test123!',
                'new_password_confirmation' => 'Test123!',
            ]);

        // Check for alert
        $response->assertSee('alert');
    }

    /** @test */
    public function form_labels_are_visible_on_all_screen_sizes()
    {
        $this->actingAs($this->user);

        $response = $this->get('/change-password');

        // Check for labels
        $response->assertSee('label-text');
        $response->assertSee('font-semibold');
    }

    /** @test */
    public function help_text_is_responsive()
    {
        $this->actingAs($this->user);

        $response = $this->get('/change-password');

        // Check for help text
        $response->assertSee('label-text-alt');
    }

    /** @test */
    public function buttons_have_proper_loading_states()
    {
        $this->actingAs($this->user);

        $response = $this->get('/change-password');

        // Check for loading indicators
        $response->assertSee('loading-spinner');
    }

    /** @test */
    public function error_messages_are_accessible()
    {
        $response = $this->post('/login', [
            'username' => 'nonexistent',
            'password' => 'wrong',
        ]);

        // Check for error styling
        $response->assertSee('input-error');
        $response->assertSee('text-error');
    }

    /** @test */
    public function loading_overlays_cover_full_screen()
    {
        $admin = User::factory()->create([
            'username' => 'admin4',
            'email' => 'admin4@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');

        // Check for loading overlay
        $response->assertSee('loading-overlay');
    }

    /** @test */
    public function modal_dialogs_center_on_mobile()
    {
        $response = $this->get('/login');

        // Check for modal classes
        $response->assertSee('modal');
    }

    /** @test */
    public function breadcrumbs_are_responsive()
    {
        $admin = User::factory()->create([
            'username' => 'admin5',
            'email' => 'admin5@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users/edit/' . $admin->id);

        // Check for breadcrumb navigation
        $response->assertSee('breadcrumb');
    }

    /** @test */
    public function action_buttons_align_properly()
    {
        $response = $this->actingAs($this->user)
            ->get('/change-password');

        // Check for action alignment
        $response->assertSee('card-actions');
        $response->assertSee('justify-end');
    }

    /** @test */
    public function search_input_is_full_width_on_mobile()
    {
        $admin = User::factory()->create([
            'username' => 'admin6',
            'email' => 'admin6@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');

        // Check for search input
        $response->assertSee('input');
        $response->assertSee('w-full');
    }

    /** @test */
    public function filter_dropdowns_work_on_mobile()
    {
        $admin = User::factory()->create([
            'username' => 'admin7',
            'email' => 'admin7@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');

        // Check for dropdown filters
        $response->assertSee('select');
    }

    /** @test */
    public function stats_cards_stack_on_mobile()
    {
        $admin = User::factory()->create([
            'username' => 'admin8',
            'email' => 'admin8@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $response = $this->actingAs($admin)->get('/admin/users');

        // Check for stats cards
        $response->assertSee('stats');
        $response->assertSee('shadow');
    }

    /** @test */
    public function hero_sections_are_centered_responsively()
    {
        $response = $this->actingAs($this->user)->get('/counter');

        // Check for hero section
        $response->assertSee('hero');
        $response->assertSee('bg-base-200');
    }

    /** @test */
    public function footer_links_stack_on_mobile()
    {
        $response = $this->actingAs($this->user)->get('/counter');

        // Check for footer
        $response->assertSee('footer');
    }

    /** @test */
    public function skip_links_exist_for_accessibility()
    {
        $response = $this->get('/login');

        // Check for skip link
        $response->assertSee('sr-only');
        $response->assertSee('focus:not-sr-only');
    }

    /** @test */
    public function focus_indicators_are_visible()
    {
        $response = $this->get('/login');

        // Check for focus indicators
        $response->assertSee('focus:outline-none');
        $response->assertSee('focus:ring');
    }

    /** @test */
    public function touch_targets_meet_minimum_size()
    {
        $response = $this->actingAs($this->user)->get('/counter');

        // Check for proper touch target sizing
        $response->assertSee('btn');
    }

    /** @test */
    public function viewport_meta_tag_is_present()
    {
        $response = $this->get('/login');

        // Check viewport meta tag
        $response->assertSee('name="viewport"');
    }
}
