<?php

namespace Tests\Feature;

use App\Domains\Communication\Models\CommunicationCampaign;
use App\Domains\Communication\Models\CommunicationRecipient;
use App\Domains\Communication\Models\UserCommunicationPreference;
use App\Domains\Communication\Models\CommunicationUnsubscribe;
use App\Domains\Communication\Services\CommunicationService;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class CommunicationSystemTest extends TestCase
{
    use DatabaseTransactions;

    protected $communicationService;
    protected $user;
    protected $testUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->communicationService = app(CommunicationService::class);
        $this->user = User::factory()->create(['email_verified_at' => now()]);
        $this->testUser = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'email_verified_at' => now(),
        ]);
    }

    public function test_can_create_campaign_with_all_users_targeting()
    {
        User::factory()->count(5)->create(['email_verified_at' => now()]);

        $data = [
            'name' => 'Test Campaign',
            'subject' => 'Test Subject',
            'html_content' => '<p>Test content</p>',
            'channels' => ['email'],
            'target_type' => 'all_users',
        ];

        $campaign = $this->communicationService->createCampaign($data, $this->user->id);

        $this->assertNotNull($campaign);
        $this->assertEquals('Test Campaign', $campaign->name);
        $this->assertEquals('draft', $campaign->status);
        $this->assertEquals(6, $campaign->total_recipients); // 5 + 1 testUser
    }

    public function test_can_target_activated_users_only()
    {
        User::factory()->create(['email_verified_at' => null]); // Inactive
        User::factory()->count(3)->create(['email_verified_at' => now()]); // Active

        $data = [
            'name' => 'Activated Users Only',
            'subject' => 'Test',
            'html_content' => '<p>Test</p>',
            'channels' => ['email'],
            'target_type' => 'activated_users',
        ];

        $campaign = $this->communicationService->createCampaign($data, $this->user->id);
        $recipients = $this->communicationService->getTargetedRecipients($campaign);

        $this->assertEquals(4, $recipients->count()); // 3 + testUser
    }

    public function test_can_target_inactive_users_only()
    {
        User::factory()->count(2)->create(['email_verified_at' => null]); // Inactive

        $data = [
            'name' => 'Inactive Users Only',
            'subject' => 'Test',
            'html_content' => '<p>Test</p>',
            'channels' => ['email'],
            'target_type' => 'inactive_users',
        ];

        $campaign = $this->communicationService->createCampaign($data, $this->user->id);
        $recipients = $this->communicationService->getTargetedRecipients($campaign);

        $this->assertEquals(2, $recipients->count());
    }

    public function test_can_target_newsletter_subscribers()
    {
        $subscriber = User::factory()->create();
        UserCommunicationPreference::create([
            'user_id' => $subscriber->id,
            'email_newsletters' => true,
        ]);

        $nonSubscriber = User::factory()->create();
        UserCommunicationPreference::create([
            'user_id' => $nonSubscriber->id,
            'email_newsletters' => false,
        ]);

        $data = [
            'name' => 'Newsletter Campaign',
            'subject' => 'Test',
            'html_content' => '<p>Test</p>',
            'channels' => ['email'],
            'target_type' => 'newsletter_subscribers',
        ];

        $campaign = $this->communicationService->createCampaign($data, $this->user->id);
        $recipients = $this->communicationService->getTargetedRecipients($campaign);

        $this->assertEquals(1, $recipients->count());
        $this->assertEquals($subscriber->id, $recipients->first()->id);
    }

    public function test_email_template_variables_replaced_correctly()
    {
        $campaign = CommunicationCampaign::create([
            'name' => 'Variable Test',
            'subject' => 'Hello {{first_name}}',
            'html_content' => '<p>Hello {{first_name}} {{last_name}}, your email is {{email}}</p>',
            'channels' => json_encode(['email']),
            'target_type' => 'all_users',
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $token = \Illuminate\Support\Str::random(64);
        \DB::table('communication_unsubscribes')->insert([
            'user_id' => $this->testUser->id,
            'unsubscribe_type' => 'email',
            'unsubscribe_token' => $token,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $content = $this->communicationService->replaceEmailVariables(
            $campaign->html_content,
            $this->testUser,
            $token
        );

        $this->assertStringContainsString('Hello John Doe', $content);
        $this->assertStringContainsString('john@example.com', $content);
        $this->assertStringNotContainsString('{{first_name}}', $content);
    }

    public function test_can_mark_recipient_as_sent()
    {
        $campaign = CommunicationCampaign::factory()->create();
        $recipient = CommunicationRecipient::create([
            'campaign_id' => $campaign->id,
            'user_id' => $this->testUser->id,
            'status' => 'pending',
        ]);

        $recipient->markAsSent();

        $this->assertEquals('sent', $recipient->status);
        $this->assertNotNull($recipient->sent_at);
    }

    public function test_can_mark_recipient_as_opened()
    {
        $campaign = CommunicationCampaign::factory()->create();
        $recipient = CommunicationRecipient::create([
            'campaign_id' => $campaign->id,
            'user_id' => $this->testUser->id,
            'status' => 'delivered',
        ]);

        $recipient->markAsOpened();

        $this->assertTrue($recipient->opened);
        $this->assertNotNull($recipient->opened_at);
        $this->assertEquals(1, $campaign->fresh()->opened_count);
    }

    public function test_can_mark_recipient_as_unsubscribed()
    {
        $campaign = CommunicationCampaign::factory()->create();
        $recipient = CommunicationRecipient::create([
            'campaign_id' => $campaign->id,
            'user_id' => $this->testUser->id,
            'status' => 'delivered',
        ]);

        $recipient->markAsUnsubscribed();

        $this->assertEquals('unsubscribed', $recipient->status);
        $this->assertEquals(1, $campaign->fresh()->unsubscribed_count);
    }

    public function test_unsubscribe_token_is_valid()
    {
        $unsub = CommunicationUnsubscribe::create([
            'user_id' => $this->testUser->id,
            'unsubscribe_type' => 'email',
            'unsubscribe_token' => CommunicationUnsubscribe::generateToken(),
        ]);

        $this->assertTrue($unsub->isValidToken());
        $this->assertEquals(64, strlen($unsub->unsubscribe_token));
    }

    public function test_unsubscribe_updates_user_preferences()
    {
        $prefs = UserCommunicationPreference::getOrCreateForUser($this->testUser->id);
        $this->assertTrue($prefs->email_newsletters);

        $unsub = CommunicationUnsubscribe::create([
            'user_id' => $this->testUser->id,
            'unsubscribe_type' => 'email',
            'unsubscribe_token' => CommunicationUnsubscribe::generateToken(),
        ]);

        $unsub->updateUserPreferences();

        $this->assertFalse($prefs->fresh()->email_newsletters);
    }

    public function test_can_check_user_unsubscribe_status()
    {
        CommunicationUnsubscribe::create([
            'user_id' => $this->testUser->id,
            'unsubscribe_type' => 'email',
            'unsubscribe_token' => CommunicationUnsubscribe::generateToken(),
        ]);

        $isUnsubscribed = $this->communicationService->isUserUnsubscribed(
            $this->testUser,
            ['email']
        );

        $this->assertTrue($isUnsubscribed);
    }

    public function test_user_preferences_prevent_delivery()
    {
        $campaign = CommunicationCampaign::factory()->create([
            'channels' => json_encode(['email']),
        ]);

        UserCommunicationPreference::create([
            'user_id' => $this->testUser->id,
            'email_newsletters' => false,
        ]);

        $canReceive = $this->communicationService->userCanReceive($this->testUser, $campaign);

        $this->assertFalse($canReceive);
    }

    public function test_campaign_analytics_calculated_correctly()
    {
        $campaign = CommunicationCampaign::create([
            'name' => 'Analytics Test',
            'subject' => 'Test',
            'html_content' => '<p>Test</p>',
            'channels' => json_encode(['email']),
            'target_type' => 'all_users',
            'status' => 'sent',
            'created_by' => $this->user->id,
            'total_recipients' => 100,
            'sent_count' => 100,
            'delivered_count' => 95,
            'opened_count' => 50,
            'clicked_count' => 20,
            'unsubscribed_count' => 5,
        ]);

        $analytics = $this->communicationService->getAnalytics($campaign);

        $this->assertEquals(100, $analytics['total_recipients']);
        $this->assertEquals(100, $analytics['sent_count']);
        $this->assertEquals(95, $analytics['delivered_count']);
        $this->assertEquals(50, $analytics['opened_count']);
        $this->assertEquals(20, $analytics['clicked_count']);
        $this->assertGreaterThan(0, $analytics['delivery_rate']);
        $this->assertGreaterThan(0, $analytics['open_rate']);
    }

    public function test_campaign_lifecycle_status_transitions()
    {
        $campaign = CommunicationCampaign::factory()->create(['status' => 'draft']);

        $campaign->markAsSending();
        $this->assertEquals('sending', $campaign->status);
        $this->assertNotNull($campaign->sent_at);

        $campaign->pause();
        $this->assertEquals('paused', $campaign->status);

        $campaign->resume();
        $this->assertEquals('sending', $campaign->status);

        $campaign->markAsCompleted();
        $this->assertEquals('sent', $campaign->status);
        $this->assertNotNull($campaign->completed_at);
    }

    public function test_bulk_recipient_insertion()
    {
        User::factory()->count(100)->create(['email_verified_at' => now()]);

        $campaign = CommunicationCampaign::create([
            'name' => 'Bulk Test',
            'subject' => 'Test',
            'html_content' => '<p>Test</p>',
            'channels' => json_encode(['email']),
            'target_type' => 'all_users',
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $this->communicationService->calculateRecipients($campaign);

        $this->assertEquals(101, $campaign->fresh()->total_recipients);
        $this->assertEquals(101, CommunicationRecipient::where('campaign_id', $campaign->id)->count());
    }

    public function test_click_tracking_with_url()
    {
        $campaign = CommunicationCampaign::factory()->create();
        $recipient = CommunicationRecipient::create([
            'campaign_id' => $campaign->id,
            'user_id' => $this->testUser->id,
            'status' => 'delivered',
        ]);

        $url = 'https://example.com/promo';
        $recipient->markAsClicked($url);

        $this->assertTrue($recipient->clicked);
        $this->assertEquals($url, $recipient->clicked_url);
        $this->assertEquals(1, $campaign->fresh()->clicked_count);
    }
}
