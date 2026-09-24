@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>{{ $campaign->name }}</h1>
            <p class="text-muted">{{ $campaign->description }}</p>
        </div>
        @if($campaign->status === 'draft')
            <div class="btn-group">
                <a href="{{ route('admin.communications.edit', $campaign) }}" class="btn btn-secondary">
                    <i class="fas fa-edit"></i> Edit
                </a>
                <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        @endif
    </div>

    <!-- Campaign Info -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Campaign Details</h5>
                </div>
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">Status</dt>
                        <dd class="col-sm-8">
                            @switch($campaign->status)
                                @case('draft')
                                    <span class="badge bg-secondary">Draft</span>
                                    @break
                                @case('scheduled')
                                    <span class="badge bg-warning">Scheduled</span>
                                    @break
                                @case('sending')
                                    <span class="badge bg-info">Sending</span>
                                    @break
                                @case('sent')
                                    <span class="badge bg-success">Sent</span>
                                    @break
                                @case('paused')
                                    <span class="badge bg-warning">Paused</span>
                                    @break
                                @case('cancelled')
                                    <span class="badge bg-danger">Cancelled</span>
                                    @break
                            @endswitch
                        </dd>

                        <dt class="col-sm-4">Subject</dt>
                        <dd class="col-sm-8">{{ $campaign->subject }}</dd>

                        <dt class="col-sm-4">Target</dt>
                        <dd class="col-sm-8"><small class="badge bg-info">{{ $campaign->getTargetLabel() }}</small></dd>

                        <dt class="col-sm-4">Channels</dt>
                        <dd class="col-sm-8">{!! $campaign->getChannelLabel() !!}</dd>

                        <dt class="col-sm-4">Schedule</dt>
                        <dd class="col-sm-8">
                            @if($campaign->schedule_type === 'immediate')
                                Send Immediately
                            @elseif($campaign->schedule_type === 'scheduled')
                                {{ $campaign->scheduled_at ? $campaign->scheduled_at->format('M d, Y H:i') : 'Not set' }}
                            @elseif($campaign->schedule_type === 'recurring')
                                Every {{ $campaign->recurrence_rule }}
                            @endif
                        </dd>

                        <dt class="col-sm-4">Created</dt>
                        <dd class="col-sm-8">{{ $campaign->created_at->format('M d, Y H:i') }}</dd>

                        <dt class="col-sm-4">Sent At</dt>
                        <dd class="col-sm-8">{{ $campaign->sent_at ? $campaign->sent_at->format('M d, Y H:i') : 'Not sent' }}</dd>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Analytics -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5>Analytics</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-center">
                                <h3>{{ number_format($analytics['total_recipients']) }}</h3>
                                <small class="text-muted">Total Recipients</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <h3>{{ number_format($analytics['sent_count']) }}</h3>
                                <small class="text-muted">Sent</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <h3>{{ $analytics['delivery_rate'] }}%</h3>
                                <small class="text-muted">Delivery Rate</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <h3>{{ $analytics['open_rate'] }}%</h3>
                                <small class="text-muted">Open Rate</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <h3>{{ $analytics['click_rate'] }}%</h3>
                                <small class="text-muted">Click Rate</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                <h3>{{ $analytics['bounce_rate'] }}%</h3>
                                <small class="text-muted">Bounce Rate</small>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3">
                        <div class="progress" style="height: 25px;">
                            <div class="progress-bar" role="progressbar"
                                 style="width: {{ $campaign->getProgressPercentage() }}%">
                                Progress: {{ $campaign->getProgressPercentage() }}%
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Preview -->
    <div class="card mb-4">
        <div class="card-header">
            <h5>Content Preview</h5>
        </div>
        <div class="card-body">
            <div style="border: 1px solid #ddd; padding: 20px; border-radius: 4px; background: #f9f9f9;">
                {!! $campaign->html_content !!}
            </div>
        </div>
    </div>

    <!-- Recipients Table -->
    <div class="card">
        <div class="card-header">
            <h5>Recipients</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Sent At</th>
                        <th>Opened</th>
                        <th>Clicked</th>
                        <th>Error</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recipients as $recipient)
                        <tr>
                            <td>{{ $recipient->user->name }}</td>
                            <td>{{ $recipient->user->email }}</td>
                            <td>
                                @switch($recipient->status)
                                    @case('pending')
                                        <span class="badge bg-secondary">Pending</span>
                                        @break
                                    @case('sent')
                                        <span class="badge bg-info">Sent</span>
                                        @break
                                    @case('delivered')
                                        <span class="badge bg-success">Delivered</span>
                                        @break
                                    @case('failed')
                                        <span class="badge bg-danger">Failed</span>
                                        @break
                                    @case('bounced')
                                        <span class="badge bg-warning">Bounced</span>
                                        @break
                                    @case('unsubscribed')
                                        <span class="badge bg-secondary">Unsubscribed</span>
                                        @break
                                @endswitch
                            </td>
                            <td>{{ $recipient->sent_at ? $recipient->sent_at->format('M d, Y H:i') : '-' }}</td>
                            <td>
                                @if($recipient->opened)
                                    <i class="fas fa-check text-success"></i> {{ $recipient->opened_at->format('M d, Y H:i') }}
                                @else
                                    <i class="fas fa-times text-muted"></i>
                                @endif
                            </td>
                            <td>
                                @if($recipient->clicked)
                                    <i class="fas fa-check text-success"></i> {{ $recipient->clicked_at->format('M d, Y H:i') }}
                                    @if($recipient->clicked_url)
                                        <br><small>{{ Str::limit($recipient->clicked_url, 30) }}</small>
                                    @endif
                                @else
                                    <i class="fas fa-times text-muted"></i>
                                @endif
                            </td>
                            <td>
                                @if($recipient->error_message)
                                    <small class="text-danger" title="{{ $recipient->error_message }}">
                                        {{ Str::limit($recipient->error_message, 20) }}
                                    </small>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">No recipients yet</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $recipients->links() }}
        </div>
    </div>
</div>

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Delete Campaign</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this campaign? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('admin.communications.destroy', $campaign) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
