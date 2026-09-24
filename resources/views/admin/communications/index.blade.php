@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>Communication Campaigns</h1>
            <p class="text-muted">Manage multi-channel campaigns across email, announcements, in-app notifications, and push</p>
        </div>
        <a href="{{ route('admin.communications.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Campaign
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text text-white-50">Total Campaigns</p>
                            <h3>{{ $stats['total_campaigns'] }}</h3>
                        </div>
                        <i class="fas fa-envelope fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text text-white-50">Active Campaigns</p>
                            <h3>{{ $stats['active_campaigns'] }}</h3>
                        </div>
                        <i class="fas fa-play fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text text-white-50">Total Sent</p>
                            <h3>{{ number_format($stats['total_sent']) }}</h3>
                        </div>
                        <i class="fas fa-check fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="card-text text-white-50">Avg Open Rate</p>
                            <h3>{{ $stats['avg_open_rate'] }}%</h3>
                        </div>
                        <i class="fas fa-chart-line fa-3x text-white-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Campaigns Table -->
    <div class="card">
        <div class="card-header">
            <h5>Campaigns</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Subject</th>
                        <th>Target</th>
                        <th>Channels</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Recipients</th>
                        <th>Sent</th>
                        <th>Open Rate</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($campaigns as $campaign)
                        <tr>
                            <td>
                                <strong>{{ $campaign->name }}</strong><br>
                                <small class="text-muted">{{ $campaign->created_at->format('M d, Y') }}</small>
                            </td>
                            <td>{{ Str::limit($campaign->subject, 30) }}</td>
                            <td>
                                <small class="badge bg-info">{{ $campaign->getTargetLabel() }}</small>
                            </td>
                            <td>
                                {!! $campaign->getChannelLabel() !!}
                            </td>
                            <td>
                                @if($campaign->status === 'draft')
                                    <span class="badge bg-secondary">Draft</span>
                                @elseif($campaign->status === 'scheduled')
                                    <span class="badge bg-warning">Scheduled</span>
                                @elseif($campaign->status === 'sending')
                                    <span class="badge bg-info">Sending</span>
                                @elseif($campaign->status === 'sent')
                                    <span class="badge bg-success">Sent</span>
                                @elseif($campaign->status === 'paused')
                                    <span class="badge bg-warning">Paused</span>
                                @elseif($campaign->status === 'cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                @endif
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{ $campaign->getProgressPercentage() }}%"
                                         aria-valuenow="{{ $campaign->getProgressPercentage() }}"
                                         aria-valuemin="0" aria-valuemax="100">
                                        {{ $campaign->getProgressPercentage() }}%
                                    </div>
                                </div>
                            </td>
                            <td>{{ number_format($campaign->total_recipients) }}</td>
                            <td>{{ number_format($campaign->sent_count) }}</td>
                            <td>{{ $campaign->getOpenRate() }}%</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.communications.show', $campaign) }}"
                                       class="btn btn-outline-primary" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($campaign->status === 'draft')
                                        <a href="{{ route('admin.communications.edit', $campaign) }}"
                                           class="btn btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    @endif
                                    @if($campaign->status === 'draft' || $campaign->status === 'scheduled')
                                        <button class="btn btn-outline-success launch-campaign"
                                                data-campaign-id="{{ $campaign->id }}" title="Launch">
                                            <i class="fas fa-paper-plane"></i>
                                        </button>
                                    @elseif($campaign->status === 'sending')
                                        <button class="btn btn-outline-warning pause-campaign"
                                                data-campaign-id="{{ $campaign->id }}" title="Pause">
                                            <i class="fas fa-pause"></i>
                                        </button>
                                    @elseif($campaign->status === 'paused')
                                        <button class="btn btn-outline-success resume-campaign"
                                                data-campaign-id="{{ $campaign->id }}" title="Resume">
                                            <i class="fas fa-play"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4">
                                <p class="text-muted mb-2">No campaigns yet</p>
                                <a href="{{ route('admin.communications.create') }}" class="btn btn-sm btn-primary">
                                    Create your first campaign
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $campaigns->links() }}
        </div>
    </div>
</div>

<script>
document.querySelectorAll('.launch-campaign').forEach(btn => {
    btn.addEventListener('click', function() {
        if (confirm('Launch this campaign?')) {
            fetch(`/admin/communications/${this.dataset.campaignId}/launch`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
            })
            .then(r => r.json())
            .then(d => {
                if (d.success) {
                    location.reload();
                } else {
                    alert('Error: ' + d.error);
                }
            });
        }
    });
});

document.querySelectorAll('.pause-campaign').forEach(btn => {
    btn.addEventListener('click', function() {
        fetch(`/admin/communications/${this.dataset.campaignId}/pause`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) location.reload();
        });
    });
});

document.querySelectorAll('.resume-campaign').forEach(btn => {
    btn.addEventListener('click', function() {
        fetch(`/admin/communications/${this.dataset.campaignId}/resume`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) location.reload();
        });
    });
});
</script>
@endsection
