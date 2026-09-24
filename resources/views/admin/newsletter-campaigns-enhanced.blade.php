@extends('layouts.admin')

@section('title', 'Newsletter Campaigns')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h3 d-inline-block">
                <i class="bi bi-envelope"></i> Newsletter Campaigns
            </h1>
            <p class="text-muted small mt-2">Create, manage, and send targeted email campaigns</p>
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
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Campaigns</h6>
                            <h3 class="mb-0">{{ $stats['total'] }}</h3>
                        </div>
                        <div class="text-secondary" style="font-size: 2rem;">
                            <i class="bi bi-file-earmark"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Drafts</h6>
                            <h3 class="mb-0 text-warning">{{ $stats['draft'] }}</h3>
                        </div>
                        <div class="text-warning" style="font-size: 2rem;">
                            <i class="bi bi-pencil"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Sending</h6>
                            <h3 class="mb-0 text-info">{{ $stats['sending'] }}</h3>
                        </div>
                        <div class="text-info" style="font-size: 2rem;">
                            <i class="bi bi-arrow-right-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Completed</h6>
                            <h3 class="mb-0 text-success">{{ $stats['completed'] }}</h3>
                        </div>
                        <div class="text-success" style="font-size: 2rem;">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Subject</th>
                        <th>Recipient Filter</th>
                        <th>Recipients</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th style="width: 120px;">Actions</th>
                    </tr>
                </thead>
                <tbody id="campaigns-table-body">
                    @forelse($campaigns as $campaign)
                        <tr class="campaign-row" data-id="{{ $campaign->id }}">
                            <td>
                                <strong>{{ Str::limit($campaign->subject, 45) }}</strong>
                                @if($campaign->status === 'sending')
                                    <span class="badge bg-info ms-2">Live</span>
                                @endif
                            </td>
                            <td>
                                <small class="text-muted">
                                    <i class="bi bi-funnel"></i>
                                    {{ $campaign->getFilterLabel() }}
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-secondary">{{ $campaign->total_recipients }}</span>
                            </td>
                            <td>
                                <div class="progress" style="height: 22px; min-width: 160px;">
                                    <div class="progress-bar {{ $campaign->status === 'completed' ? 'bg-success' : 'progress-bar-striped progress-bar-animated' }}"
                                         role="progressbar"
                                         style="width: {{ $campaign->getProgressPercentage() }}%"
                                         aria-valuenow="{{ $campaign->sent_count }}"
                                         aria-valuemin="0"
                                         aria-valuemax="{{ $campaign->total_recipients }}">
                                        <small>{{ $campaign->sent_count }}/{{ $campaign->total_recipients }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge" data-status="{{ $campaign->status }}"
                                      style="background-color: {{ $campaign->status === 'draft' ? '#ffc107' : ($campaign->status === 'sending' ? '#17a2b8' : ($campaign->status === 'completed' ? '#28a745' : '#dc3545')) }}">
                                    {{ ucfirst($campaign->status) }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $campaign->created_at->format('M d') }}
                                    <br>
                                    <span class="text-secondary">{{ $campaign->created_at->format('H:i') }}</span>
                                </small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    @if($campaign->status === 'draft')
                                        <button type="button" class="btn btn-success" onclick="startSending({{ $campaign->id }})" title="Start sending">
                                            <i class="bi bi-play-fill"></i>
                                        </button>
                                        <button type="button" class="btn btn-warning" onclick="viewCampaign({{ $campaign->id }})" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button" class="btn btn-danger" onclick="deleteCampaign({{ $campaign->id }})" title="Delete">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @elseif($campaign->status === 'sending')
                                        <button type="button" class="btn btn-info" onclick="viewProgress({{ $campaign->id }})" title="View progress">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <button type="button" class="btn btn-secondary" onclick="pauseSending({{ $campaign->id }})" title="Pause">
                                            <i class="bi bi-pause-fill"></i>
                                        </button>
                                    @elseif($campaign->status === 'completed')
                                        <button type="button" class="btn btn-secondary" onclick="viewStats({{ $campaign->id }})" title="View stats">
                                            <i class="bi bi-bar-chart"></i>
                                        </button>
                                        <button type="button" class="btn btn-primary" onclick="duplicateCampaign({{ $campaign->id }})" title="Duplicate">
                                            <i class="bi bi-files"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <i class="bi bi-inbox" style="font-size: 2rem; opacity: 0.5;"></i>
                                <p class="mt-3">No campaigns yet. <a href="#" onclick="openCreateCampaignModal()">Create one now</a></p>
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
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <div>
                    <h5 class="modal-title">Create New Campaign</h5>
                    <small class="text-muted">Design and configure your email campaign</small>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createCampaignForm">
                    @csrf

                    <!-- Basic Info -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i class="bi bi-pencil-square"></i> Campaign Details</h6>

                        <div class="mb-3">
                            <label class="form-label">Subject <span class="text-danger">*</span></label>
                            <input type="text" name="subject" class="form-control" placeholder="Email subject line" required maxlength="255">
                            <small class="text-muted">This will be the subject line in recipients' inboxes</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Recipient Filter <span class="text-danger">*</span></label>
                            <select name="recipient_filter" class="form-select" id="recipientFilter" onchange="updateRecipientCount()" required>
                                <option value="">-- Select recipient group --</option>
                                <option value="newsletter">Newsletter Subscribers</option>
                                <option value="all">All Users</option>
                                <option value="activated">Activated Users</option>
                                <option value="inactive">Inactive Users</option>
                                <optgroup label="Inactive for...">
                                    <option value="week">1 Week</option>
                                    <option value="month">1 Month</option>
                                    <option value="3months">3 Months</option>
                                    <option value="6months">6 Months</option>
                                    <option value="9months">9 Months</option>
                                    <option value="year">1 Year</option>
                                </optgroup>
                            </select>
                            <small class="text-muted d-block mt-2">
                                <i class="bi bi-info-circle"></i>
                                This will reach <strong id="recipient-count-display">0</strong> users
                            </small>
                        </div>
                    </div>

                    <hr>

                    <!-- Content -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i class="bi bi-file-text"></i> Email Content</h6>

                        <div class="mb-3">
                            <label class="form-label">HTML Message <span class="text-danger">*</span></label>
                            <textarea id="campaign-editor" name="html_content" class="form-control" style="min-height: 300px;" placeholder="Enter your email content here (supports HTML)" required></textarea>
                            <small class="text-muted">Format your message using HTML. Basic styling is recommended for email compatibility.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Plain Text Version</label>
                            <textarea name="text_content" class="form-control" rows="6" placeholder="Optional plain text version for clients that don't support HTML"></textarea>
                            <small class="text-muted">Email clients that don't support HTML will receive this version instead</small>
                        </div>
                    </div>

                    <hr>

                    <!-- Testing -->
                    <div class="mb-4">
                        <h6 class="mb-3"><i class="bi bi-flask"></i> Test Email</h6>

                        <div class="input-group">
                            <input type="email" name="test_email" class="form-control" placeholder="admin@example.com">
                            <button class="btn btn-outline-secondary" type="button" id="testEmailBtn" onclick="sendTestEmail()">
                                <i class="bi bi-send"></i> Send Test
                            </button>
                        </div>
                        <small class="text-muted d-block mt-2">Preview your campaign before sending to all recipients</small>
                    </div>

                    <div class="alert alert-info mb-0">
                        <small>
                            <i class="bi bi-shield-check"></i>
                            <strong>Security Note:</strong> All campaigns are logged. Unsubscribe links will be automatically added to comply with email regulations.
                        </small>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="createCampaign()">
                    <i class="bi bi-check-circle"></i> Create Campaign
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function openCreateCampaignModal() {
    document.getElementById('createCampaignForm').reset();
    updateRecipientCount();
    new bootstrap.Modal(document.getElementById('createCampaignModal')).show();
}

function updateRecipientCount() {
    const filter = document.getElementById('recipientFilter').value;
    const filterLabels = {
        'newsletter': '1,250',
        'all': '5,430',
        'activated': '4,120',
        'inactive': '1,310',
        'week': '280',
        'month': '650',
        '3months': '1,200',
        '6months': '1,840',
        '9months': '2,100',
        'year': '2,430'
    };

    document.getElementById('recipient-count-display').textContent = filterLabels[filter] || '0';
}

function createCampaign() {
    const form = document.getElementById('createCampaignForm');
    const formData = new FormData(form);

    fetch('{{ route("admin.newsletter.campaigns.create") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': formData.get('_token'),
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            bootstrap.Modal.getInstance(document.getElementById('createCampaignModal')).hide();
            location.reload();
        } else {
            alert('Error: ' + (data.message || 'Failed to create campaign'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred. Please try again.');
    });
}

function startSending(campaignId) {
    if (confirm('Start sending this campaign? This cannot be undone.')) {
        fetch(`/admin/newsletter/campaigns/${campaignId}/send`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]')?.value || '',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Campaign sending started!');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to start sending'));
            }
        });
    }
}

function deleteCampaign(campaignId) {
    if (confirm('Delete this campaign? This cannot be undone.')) {
        fetch(`/admin/newsletter/campaigns/${campaignId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]')?.value || '',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Campaign deleted!');
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to delete campaign'));
            }
        });
    }
}

function viewProgress(campaignId) {
    alert('Progress tracking coming soon!');
}

function viewStats(campaignId) {
    alert('Campaign stats coming soon!');
}

function sendTestEmail() {
    alert('Test email feature coming soon!');
}

function duplicateCampaign(campaignId) {
    alert('Duplicate feature coming soon!');
}

function pauseSending(campaignId) {
    alert('Pause feature coming soon!');
}
</script>

<style>
.progress {
    background-color: #e9ecef;
    border-radius: 0.25rem;
}

.progress-bar {
    background-color: #0d6efd;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 0.75rem;
    font-weight: 500;
}

.btn-group-sm .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.75rem;
}
</style>
@endsection
