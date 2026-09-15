<?php

namespace Tests\Unit\Core\Traits;

use App\Models\Ban;
use App\Models\User;
use Tests\TestCase;

class HasBansTraitTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_be_banned(): void
    {
        $ban = $this->user->ban('Test ban');

        $this->assertNotNull($ban);
        $this->assertTrue($this->user->isBanned());
    }

    public function test_user_can_be_unbanned(): void
    {
        $this->user->ban('Test ban');
        $this->assertTrue($this->user->isBanned());

        $this->user->unban();
        $this->user->refresh();
        $this->assertFalse($this->user->isBanned());
    }

    public function test_user_can_have_multiple_bans(): void
    {
        $this->user->ban('Ban 1');
        $this->user->ban('Ban 2', now()->addDays(7));

        $this->assertEqual($this->user->bans()->count(), 2);
    }

    public function test_can_get_active_ban(): void
    {
        $ban = $this->user->ban('Active ban');

        $activeBan = $this->user->activeBan();
        $this->assertNotNull($activeBan);
        $this->assertEqual($activeBan->id, $ban->id);
    }

    public function test_permanent_ban_is_active(): void
    {
        $ban = $this->user->ban('Permanent ban');

        $this->assertTrue($ban->isPermanent());
        $this->assertTrue($ban->isActive());
    }

    public function test_temporary_ban_expires(): void
    {
        $expiresAt = now()->addDays(1);
        $ban = $this->user->ban('Temporary ban', $expiresAt);

        $this->assertFalse($ban->isPermanent());
        $this->assertTrue($ban->isActive());
    }
}
