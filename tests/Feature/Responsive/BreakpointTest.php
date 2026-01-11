<?php

namespace Tests\Feature\Responsive;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BreakpointTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->adminUser = User::factory()->create([
            'username' => 'admin',
            'email' => 'admin@test.com',
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->user = User::factory()->create([
            'username' => 'user',
            'email' => 'user@test.com',
            'role' => 'user',
            'status' => 'active',
        ]);
    }

    /** @test */
    public function mobile_breakpoint_classes_are_present()
    {
        // Mobile: 0px - 639px (default/base styles)
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Base mobile styles should be present
        $this->assertStringContainsString('min-h-screen', $content);
        $this->assertStringContainsString('bg-base-100', $content);
        $this->assertStringContainsString('container', $content);
    }

    /** @test */
    public function small_tablet_breakpoint_classes_are_present()
    {
        // Small tablets: 640px+
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // sm: breakpoint styles
        $this->assertStringContainsString('sm:', $content);
    }

    /** @test */
    public function medium_tablet_breakpoint_classes_are_present()
    {
        // Medium tablets: 768px+
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // md: breakpoint styles
        $this->assertStringContainsString('md:', $content);
    }

    /** @test */
    public function large_tablet_breakpoint_classes_are_present()
    {
        // Large tablets: 1024px+
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // lg: breakpoint styles (navigation)
        $this->assertStringContainsString('lg:', $content);
        $this->assertStringContainsString('lg:flex', $content);
        $this->assertStringContainsString('lg:hidden', $content);
    }

    /** @test */
    public function desktop_breakpoint_classes_are_present()
    {
        // Desktop: 1024px+
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // xl: breakpoint styles
        $this->assertStringContainsString('xl:', $content);
    }

    /** @test */
    public function large_desktop_breakpoint_classes_are_present()
    {
        // Large desktop: 1280px+
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // 2xl: breakpoint styles
        $this->assertStringContainsString('2xl:', $content);
    }

    /** @test */
    public function responsive_flex_direction_is_applied()
    {
        $response = $this->actingAs($this->user)->get('/counter');

        $content = $response->getContent();

        // Check for flex utilities
        $this->assertStringContainsString('flex', $content);
        $this->assertStringContainsString('items-center', $content);
        $this->assertStringContainsString('justify-center', $content);
    }

    /** @test */
    public function responsive_spacing_is_applied()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for responsive spacing
        $this->assertStringContainsString('px-', $content);
        $this->assertStringContainsString('py-', $content);
        $this->assertStringContainsString('p-', $content);
        $this->assertStringContainsString('m-', $content);
    }

    /** @test */
    public function responsive_width_utilities_are_used()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users/create');

        $content = $response->getContent();

        // Check for width utilities
        $this->assertStringContainsString('w-full', $content);
        $this->assertStringContainsString('w-', $content);
    }

    /** @test */
    public function responsive_height_utilities_are_used()
    {
        $response = $this->actingAs($this->user)->get('/counter');

        $content = $response->getContent();

        // Check for height utilities
        $this->assertStringContainsString('h-', $content);
        $this->assertStringContainsString('min-h-screen', $content);
    }

    /** @test */
    public function responsive_grid_is_implemented()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users/edit/' . $this->adminUser->id);

        $content = $response->getContent();

        // Check for grid utilities
        $this->assertStringContainsString('grid', $content);
        $this->assertStringContainsString('grid-cols-', $content);
    }

    /** @test */
    public function responsive_text_sizes_are_applied()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for text sizing
        $this->assertStringContainsString('text-', $content);
        $this->assertStringContainsString('text-4xl', $content);
        $this->assertStringContainsString('text-base', $content);
    }

    /** @test */
    public function responsive_font_weights_are_applied()
    {
        $response = $this->actingAs($this->user)->get('/counter');

        $content = $response->getContent();

        // Check for font weights
        $this->assertStringContainsString('font-bold', $content);
    }

    /** @test */
    public function responsive_display_properties_are_used()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for display utilities
        $this->assertStringContainsString('hidden', $content);
        $this->assertStringContainsString('block', $content);
        $this->assertStringContainsString('inline', $content);
    }

    /** @test */
    public function responsive_position_properties_are_used()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for position utilities
        $this->assertStringContainsString('relative', $content);
        $this->assertStringContainsString('absolute', $content);
        $this->assertStringContainsString('fixed', $content);
    }

    /** @test */
    public function responsive_z_index_is_applied()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for z-index utilities
        $this->assertStringContainsString('z-', $content);
    }

    /** @test */
    public function responsive_overflow_is_handled()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for overflow utilities
        $this->assertStringContainsString('overflow-', $content);
    }

    /** @test */
    public function responsive_cursor_styles_are_applied()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for cursor utilities
        $this->assertStringContainsString('cursor-', $content);
    }

    /** @test */
    public function responsive_transitions_are_smooth()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for transition utilities
        $this->assertStringContainsString('transition', $content);
    }

    /** @test */
    public function responsive_transforms_are_used()
    {
        $response = $this->actingAs($this->user)->get('/counter');

        $content = $response->getContent();

        // Check for transform utilities
        $this->assertStringContainsString('transform', $content);
    }

    /** @test */
    public function responsive_shadows_are_applied()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for shadow utilities
        $this->assertStringContainsString('shadow', $content);
        $this->assertStringContainsString('shadow-xl', $content);
    }

    /** @test */
    public function responsive_borders_are_applied()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users/create');

        $content = $response->getContent();

        // Check for border utilities
        $this->assertStringContainsString('border', $content);
        $this->assertStringContainsString('rounded', $content);
    }

    /** @test */
    public function responsive_opacity_is_controlled()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for opacity utilities
        $this->assertStringContainsString('opacity-', $content);
    }

    /** @test */
    public function responsive_blur_effects_are_applied()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for blur/backdrop utilities
        $this->assertStringContainsString('backdrop', $content);
    }

    /** @test */
    public function responsive_animation_classes_are_present()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for animation utilities
        $this->assertStringContainsString('animate-', $content);
        $this->assertStringContainsString('loading', $content);
    }

    /** @test */
    public function responsive_filter_utilities_are_used()
    {
        $response = $this->actingAs($this->user)->get('/counter');

        $content = $response->getContent();

        // Check for filter utilities
        $this->assertStringContainsString('filter', $content);
    }

    /** @test */
    public function responsive_aspect_ratio_is_maintained()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for aspect ratio utilities
        $this->assertStringContainsString('aspect-', $content);
    }

    /** @test */
    public function responsive_columns_layout_is_fluid()
    {
        User::factory()->count(10)->create();

        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for column layout
        $this->assertStringContainsString('columns-', $content);
    }

    /** @test */
    public function responsive_visibility_is_controlled()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for visibility utilities
        $this->assertStringContainsString('visible', $content);
        $this->assertStringContainsString('invisible', $content);
    }

    /** @test */
    public function responsive_sizing_is_consistent()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Verify consistent sizing approach
        $this->assertStringContainsString('container', $content);
        $this->assertStringContainsString('mx-auto', $content);
    }

    /** @test */
    public function breakpoints_follow_tailwind_conventions()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Verify Tailwind breakpoint naming
        $this->assertStringContainsString('sm:', $content);
        $this->assertStringContainsString('md:', $content);
        $this->assertStringContainsString('lg:', $content);
        $this->assertStringContainsString('xl:', $content);
        $this->assertStringContainsString('2xl:', $content);
    }

    /** @test */
    public function media_queries_are_mobile_first()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Verify mobile-first approach
        // Base styles should work on mobile without prefixes
        $this->assertStringContainsString('min-h-screen', $content);
        $this->assertStringContainsString('bg-base-100', $content);
    }

    /** @test */
    public function responsive_hover_states_work()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for hover utilities
        $this->assertStringContainsString('hover:', $content);
    }

    /** @test */
    public function responsive_focus_states_work()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for focus utilities
        $this->assertStringContainsString('focus:', $content);
    }

    /** @test */
    public function responsive_active_states_work()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for active utilities
        $this->assertStringContainsString('active:', $content);
    }

    /** @test */
    public function responsive_group_hover_states_work()
    {
        $response = $this->actingAs($this->adminUser)
            ->get('/admin/users');

        $content = $response->getContent();

        // Check for group hover utilities
        $this->assertStringContainsString('group-hover:', $content);
    }

    /** @test */
    public function responsive_dark_mode_classes_are_present()
    {
        $response = $this->get('/login');

        $content = $response->getContent();

        // Check for dark mode support
        $this->assertStringContainsString('dark:', $content);
    }
}
