@extends('layouts.admin')

@section('title', 'Newsletter Campaigns')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 d-inline-block">Newsletter Campaigns</h1>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" onclick="openCreateCampaignModal()">
                <i class="bi bi-plus-circle"></i> New Campaign
            </button>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Campaigns</h6>
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Draft</h6>
                    <h3 class="mb-0 text-warning">{{ $stats['draft'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Sending</h6>
                    <h3 class="mb-0 text-info">{{ $stats['sending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Completed</h6>
                    <h3 class="mb-0 text-success">{{ $stats['completed'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Subject</th>
                        <th>Recipients</th>
                        <th>Sent</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="campaigns-table-body">
                    @forelse($campaigns as $campaign)
                        <tr class="campaign-row" data-id="{{ $campaign->id }}">
                            <td>
                                <strong>{{ Str::limit($campaign->subject, 50) }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $campaign->total_recipients }}</span>
                            </td>
                            <td>
                                <span class="sent-count">{{ $campaign->sent_count }}</span>
                            </td>
                            <td>
                                <div class="progress" style="height: 20px; min-width: 150px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated campaign-progress"
                                         style="width: {{ $campaign->getProgressPercentage() }}%">
                                        <span class="progress-text">{{ number_format($campaign->getProgressPercentage(), 1) }}%</span>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge status-badge" data-status="{{ $campaign->status }}">
                                    {{ ucfirst($campaign->status) }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $campaign->created_at->format('M d, H:i') }}</small>
                            </td>
                            <td>
                                @if($campaign->status === 'draft')
                                    <button class="btn btn-sm btn-success" onclick="startSending({{ $campaign->id }})" title="Start sending">
                                        <i class="bi bi-play-fill"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteCampaign({{ $campaign->id }})" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                @elseif($campaign->status === 'sending')
                                    <button class="btn btn-sm btn-info" onclick="viewProgress({{ $campaign->id }})" title="View progress">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                @elseif($campaign->status === 'completed')
                                    <button class="btn btn-sm btn-secondary" onclick="viewStats({{ $campaign->id }})" title="View stats">
                                        <i class="bi bi-bar-chart"></i>
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                No campaigns yet. <a href="#" onclick="openCreateCampaignModal()">Create one now</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($campaigns->hasPages())
        <div class="d-flex justify-content-center mt-4">
            {{ $campaigns->links() }}
        </div>
    @endif
</div>

<!-- Create Campaign Modal -->
<div class="modal fade" id="createCampaignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createCampaignForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Subject <span class="text-danger">*</span></label>
                        <input type="text" name="subject" class="form-control" placeholder="Email subject" required maxlength="255">
                        <small class="text-muted">Used as email subject line</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea id="campaign-editor" name="html_content" class="form-control tinymce-editor" required></textarea>
                        <small class="text-muted">HTML content for email body</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Plain Text Version</label>
                        <textarea name="text_content" class="form-control" rows="3" placeholder="Optional plain text version for email clients that don't support HTML"></textarea>
                    </div>

                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="bi bi-info-circle"></i>
                            This will be sent to <strong id="subscriber-count">0</strong> active subscribers.
                            Unsubscribe links will be automatically added.
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createCampaign()">
                    <i class="bi bi-check-circle"></i> Create Campaign
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Send Campaign Modal -->
<div class="modal fade" id="sendCampaignModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <h6>Campaign: <span id="send-subject" class="text-primary"></span></h6>
                    <p class="text-muted mb-0">Recipients: <span id="send-total" class="badge bg-secondary"></span></p>
                </div>

                <div class="mb-4">
                    <label class="form-label">Send Method</label>
                    <div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sendMethod" id="sendAllAtOnce" value="all" checked>
                            <label class="form-check-label" for="sendAllAtOnce">
                                Send all at once (faster, may take 1-5 minutes)
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="sendMethod" id="sendInBatches" value="batch">
                            <label class="form-check-label" for="sendInBatches">
                                Send in batches (slower, shows real-time progress)
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Progress Section (hidden initially) -->
                <div id="progressSection" style="display: none;">
                    <h6 class="mb-3">Sending Progress</h6>
                    <div class="progress mb-3" style="height: 25px;">
                        <div id="sendProgress" class="progress-bar progress-bar-striped progress-bar-animated"
                             style="width: 0%">
                            <span id="progressText">0%</span>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <small class="text-muted">Sent: <strong id="sentCount">0</strong></small>
                        </div>
                        <div class="col-md-6 text-end">
                            <small class="text-muted">Remaining: <strong id="remainingCount">0</strong></small>
                        </div>
                    </div>
                    <div id="statusMessage" class="alert alert-info mb-0">
                        <small>Sending emails...</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="cancelSendBtn">Cancel</button>
                <button type="button" class="btn btn-success" onclick="confirmSend()" id="confirmSendBtn">
                    <i class="bi bi-send"></i> Send Campaign
                </button>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .campaign-row {
        transition: background-color 0.2s;
    }

    .campaign-row:hover {
        background-color: #f8f9fa;
    }

    .progress-text {
        color: white;
        font-size: 12px;
        font-weight: bold;
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
    }

    .status-badge {
        font-weight: 600;
        padding: 0.5rem 0.75rem;
    }

    .status-badge[data-status="draft"] {
        background-color: #ffc107 !important;
        color: #000;
    }

    .status-badge[data-status="sending"] {
        background-color: #17a2b8 !important;
    }

    .status-badge[data-status="completed"] {
        background-color: #28a745 !important;
    }

    .status-badge[data-status="failed"] {
        background-color: #dc3545 !important;
    }

    .tinymce-editor {
        min-height: 300px;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/6.8.2/tinymce.min.js"></script>
<script>
// Initialize TinyMCE
tinymce.init({
    selector: '.tinymce-editor',
    plugins: ['advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview', 'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen', 'insertdatetime', 'media', 'table', 'help', 'wordcount'],
    toolbar: 'undo redo | formatselect | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help',
    height: 300,
    setup: function(editor) {
        editor.on('change', function() {
            tinymce.triggerSave();
        });
    }
});

let currentCampaignId = null;
let sendInProgress = false;

// Open create campaign modal
function openCreateCampaignModal() {
    document.getElementById('createCampaignForm').reset();
    tinymce.get('campaign-editor').setContent('');
    const modal = new bootstrap.Modal(document.getElementById('createCampaignModal'));
    modal.show();
}

// Create campaign
async function createCampaign() {
    const form = document.getElementById('createCampaignForm');
    const formData = new FormData(form);

    try {
        const response = await fetch('{{ route("admin.newsletter.campaigns.create") }}', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            },
            body: formData
        });

        if (!response.ok) throw new Error('Failed to create campaign');

        const campaign = await response.json();
        showToast('Campaign created successfully!', 'success');

        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('createCampaignModal')).hide();

        // Reload table
        setTimeout(() => location.reload(), 1000);
    } catch (error) {
        showToast('Error: ' + error.message, 'danger');
    }
}

// Start sending - show send modal
function startSending(campaignId) {
    currentCampaignId = campaignId;
    const row = document.querySelector(`[data-id="${campaignId}"]`);

    document.getElementById('send-subject').textContent = row.querySelector('strong').textContent;
    document.getElementById('send-total').textContent = row.querySelector('.badge').textContent;

    const modal = new bootstrap.Modal(document.getElementById('sendCampaignModal'));
    modal.show();
}

// Confirm and start send
async function confirmSend() {
    const sendMethod = document.querySelector('input[name="sendMethod"]:checked').value;

    if (!currentCampaignId) return;

    document.getElementById('confirmSendBtn').disabled = true;
    document.getElementById('progressSection').style.display = 'block';

    if (sendMethod === 'all') {
        await sendAllAtOnce();
    } else {
        await sendInBatches();
    }
}

// Send all emails at once
async function sendAllAtOnce() {
    try {
        const response = await fetch(`{{ url('/admin/newsletter/campaigns') }}/${currentCampaignId}/send`, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) throw new Error('Failed to start sending');

        updateProgressInterval();
    } catch (error) {
        showToast('Error: ' + error.message, 'danger');
        document.getElementById('confirmSendBtn').disabled = false;
    }
}

// Send in batches with progress
async function sendInBatches() {
    const batchSize = 50;
    let completed = false;

    while (!completed && !sendInProgress === false) {
        try {
            const response = await fetch(`{{ url('/admin/newsletter/campaigns') }}/${currentCampaignId}/send-batch`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                    'Accept': 'application/json',
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: `batch_size=${batchSize}`
            });

            if (!response.ok) throw new Error('Failed to send batch');

            const result = await response.json();
            updateProgressUI(result);

            if (result.completed) {
                completed = true;
                showToast('Campaign sending completed!', 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                // Wait 2 seconds before next batch
                await new Promise(r => setTimeout(r, 2000));
            }
        } catch (error) {
            showToast('Error: ' + error.message, 'danger');
            break;
        }
    }
}

// Update progress UI from batch result
function updateProgressUI(result) {
    const progress = result.progress || 0;
    const progressBar = document.getElementById('sendProgress');
    const progressText = document.getElementById('progressText');
    const sentCount = document.getElementById('sentCount');
    const remainingCount = document.getElementById('remainingCount');

    progressBar.style.width = progress + '%';
    progressText.textContent = Math.round(progress) + '%';
    sentCount.textContent = result.sent || 0;
    remainingCount.textContent = result.remaining || 0;

    // Update row in table
    if (currentCampaignId) {
        const row = document.querySelector(`[data-id="${currentCampaignId}"]`);
        if (row) {
            row.querySelector('.campaign-progress').style.width = progress + '%';
            row.querySelector('.sent-count').textContent = (result.sent || 0);
        }
    }
}

// Update progress at intervals
function updateProgressInterval() {
    const interval = setInterval(async () => {
        try {
            const response = await fetch(`{{ url('/admin/newsletter/campaigns') }}/${currentCampaignId}/status`, {
                headers: { 'Accept': 'application/json' }
            });

            const campaign = await response.json();
            updateProgressUI(campaign);

            if (campaign.status === 'completed') {
                clearInterval(interval);
                showToast('Campaign sending completed!', 'success');
                setTimeout(() => location.reload(), 2000);
            }
        } catch (error) {
            console.error('Error updating progress:', error);
        }
    }, 3000); // Update every 3 seconds
}

// Delete campaign
async function deleteCampaign(campaignId) {
    if (!confirm('Are you sure? This action cannot be undone.')) return;

    try {
        const response = await fetch(`{{ url('/admin/newsletter/campaigns') }}/${campaignId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-Token': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json'
            }
        });

        if (!response.ok) throw new Error('Failed to delete campaign');

        showToast('Campaign deleted', 'success');
        setTimeout(() => location.reload(), 1000);
    } catch (error) {
        showToast('Error: ' + error.message, 'danger');
    }
}

// View progress
function viewProgress(campaignId) {
    currentCampaignId = campaignId;
    document.getElementById('progressSection').style.display = 'block';
    document.getElementById('confirmSendBtn').style.display = 'none';

    const modal = new bootstrap.Modal(document.getElementById('sendCampaignModal'));
    modal.show();

    updateProgressInterval();
}

// Show toast notification
function showToast(message, type = 'info') {
    const toastHtml = `
        <div class="toast align-items-center text-white bg-${type} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">${message}</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    `;

    const toastContainer = document.createElement('div');
    toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
    toastContainer.innerHTML = toastHtml;
    document.body.appendChild(toastContainer);

    const toast = new bootstrap.Toast(toastContainer.querySelector('.toast'));
    toast.show();

    setTimeout(() => toastContainer.remove(), 5000);
}

// Fetch subscriber count on page load
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const response = await fetch('{{ route("newsletter.subscribe") }}', {
            headers: { 'Accept': 'application/json' }
        });
        // You may want to add an endpoint to get subscriber count
        // For now, update this manually or fetch from another endpoint
    } catch (error) {
        console.error('Error fetching subscriber count:', error);
    }
});
</script>
@endpush
@endsection
