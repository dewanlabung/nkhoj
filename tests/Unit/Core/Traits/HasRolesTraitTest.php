<?php

namespace Tests\Unit\Core\Traits;

use App\Models\User;
use Tests\TestCase;

class HasRolesTraitTest extends TestCase
{
    public function test_admin_role_label(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertEqual($user->roleLabel(), 'Super Admin');
    }

    public function test_editor_role_label(): void
    {
        $user = User::factory()->create(['role' => 'editor']);

        $this->assertEqual($user->roleLabel(), 'Editor');
    }

    public function test_reporter_role_label(): void
    {
        $user = User::factory()->create(['role' => 'reporter']);

        $this->assertEqual($user->roleLabel(), 'Author');
    }

    public function test_admin_is_admin(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($user->isAdmin());
        $this->assertTrue($user->isMod());
    }

    public function test_editor_is_mod_but_not_admin(): void
    {
        $user = User::factory()->create(['role' => 'editor']);

        $this->assertFalse($user->isAdmin());
        $this->assertTrue($user->isMod());
        $this->assertTrue($user->isEditor());
    }

    public function test_member_is_not_mod(): void
    {
        $user = User::factory()->create(['role' => 'member']);

        $this->assertFalse($user->isAdmin());
        $this->assertFalse($user->isMod());
        $this->assertFalse($user->isEditor());
    }

    public function test_admin_has_all_permissions(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($user->hasPermission('anything'));
    }

    public function test_user_has_extra_permissions(): void
    {
        $user = User::factory()->create([
            'role' => 'member',
            'extra_permissions' => ['publish_posts', 'moderate_comments']
        ]);

        $this->assertTrue($user->hasPermission('publish_posts'));
        $this->assertTrue($user->hasPermission('moderate_comments'));
        $this->assertFalse($user->hasPermission('delete_users'));
    }

    public function test_role_badge_class(): void
    {
        $adminUser = User::factory()->create(['role' => 'admin']);
        $editorUser = User::factory()->create(['role' => 'editor']);

        $this->assertStringContainsString('red', $adminUser->roleBadgeClass());
        $this->assertStringContainsString('purple', $editorUser->roleBadgeClass());
    }
}
