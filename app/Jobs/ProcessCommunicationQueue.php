<?php

namespace App\Jobs;

use App\Domains\Communication\Models\CommunicationRecipient;
use App\Domains\Communication\Models\CommunicationCampaign;
use App\Domains\Communication\Services\CommunicationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessCommunicationQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recipient;
    protected $campaign;
    private $communicationService;

    public $maxExceptions = 3;
    public $maxTries = 3;
    public $timeout = 120;

    public function __construct(CommunicationRecipient $recipient, CommunicationCampaign $campaign)
    {
        $this->recipient = $recipient;
        $this->campaign = $campaign;
    }

    public function handle(CommunicationService $communicationService): void
    {
        $this->communicationService = $communicationService;

        try {
            $recipient = CommunicationRecipient::find($this->recipient->id);

            if (!$recipient || $recipient->status !== 'pending') {
                return;
            }

            $campaign = CommunicationCampaign::find($this->campaign->id);

            if (!$campaign) {
                return;
            }

            $this->communicationService->sendToRecipient($recipient, $campaign);

        } catch (\Exception $e) {
            $this->recipient->setError($e->getMessage());

            if ($this->attempts() < $this->maxTries) {
                $this->release(300); // Retry after 5 minutes
            }

            throw $e;
        }
    }

    public function failed(\Throwable $exception): void
    {
        $this->recipient->setError('Failed after ' . $this->maxTries . ' attempts: ' . $exception->getMessage());
    }

    public function tags(): array
    {
        return ['communication', 'campaign:' . $this->campaign->id];
    }
}
