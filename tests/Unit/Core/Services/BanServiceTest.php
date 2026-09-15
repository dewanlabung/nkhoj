<?php

namespace Tests\Unit\Core\Services;

use App\Core\Contracts\BanService;
use App\Models\Ban;
use App\Models\User;
use Tests\TestCase;

class BanServiceTest extends TestCase
{
    private BanService $service;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(BanService::class);
        $this->user = User::factory()->create();
    }

    public function test_can_ban_user_permanently(): void
    {
        $ban = $this->service->ban($this->user, 'Spam content');

        $this->assertNotNull($ban);
        $this->assertTrue($ban->isPermanent());
        $this->assertTrue($ban->isActive());
    }

    public function test_can_ban_user_with_expiry(): void
    {
        $expiresAt = now()->addDays(7);
        $ban = $this->service->ban($this->user, 'Temporary ban', $expiresAt);

        $this->assertNotNull($ban);
        $this->assertFalse($ban->isPermanent());
        $this->assertTrue($ban->isActive());
    }

    public function test_can_unban_user(): void
    {
        $this->service->ban($this->user, 'Spam');
        $this->assertTrue($this->service->isActive($this->user));

        $this->service->unban($this->user);
        $this->assertFalse($this->service->isActive($this->user));
    }

    public function test_can_get_active_ban(): void
    {
        $ban = $this->service->ban($this->user, 'Test ban');

        $activeBan = $this->service->getActiveBan($this->user);
        $this->assertNotNull($activeBan);
        $this->assertEqual($activeBan->id, $ban->id);
    }

    public function test_expired_ban_is_not_active(): void
    {
        $expiresAt = now()->subHours(1);
        $ban = Ban::create([
            'bannable_type' => User::class,
            'bannable_id' => $this->user->id,
            'comment' => 'Expired ban',
            'expired_at' => $expiresAt,
        ]);

        $this->assertFalse($this->service->isActive($this->user));
    }
}
