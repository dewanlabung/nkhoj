@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h1>Create Communication Campaign</h1>
        <p class="text-muted">Create a unified campaign across multiple channels</p>
    </div>

    <form action="{{ route('admin.communications.store') }}" method="POST" class="needs-validation" novalidate>
        @csrf

        <div class="row">
            <div class="col-lg-8">
                <!-- Basic Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Campaign Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Campaign Name *</label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror"
                                   id="name" name="name" required value="{{ old('name') }}">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="2">{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">Subject Line *</label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                   id="subject" name="subject" required value="{{ old('subject') }}">
                            @error('subject') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                </div>

                <!-- Content -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Content</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="html_content" class="form-label">HTML Content *</label>
                            <textarea class="form-control @error('html_content') is-invalid @enderror"
                                      id="html_content" name="html_content" rows="8" required>{{ old('html_content') }}</textarea>
                            <small class="text-muted">Available variables: {{'{{'}}first_name{{'}}'}}, {{'{{'}}last_name{{'}}'}}, {{'{{'}}email{{'}}'}}, {{'{{'}}username{{'}}'}}, {{'{{'}}unsubscribe_link{{'}}'}}, {{'{{'}}year{{'}}'}}, {{'{{'}}current_date{{'}}'}}</small>
                            @error('html_content') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label for="text_content" class="form-label">Text Content (Optional)</label>
                            <textarea class="form-control" id="text_content" name="text_content" rows="6">{{ old('text_content') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Use Template</label>
                            <select class="form-select" id="template-select">
                                <option value="">-- No Template --</option>
                                @foreach($templates as $template)
                                    <option value="{{ $template->id }}" data-html="{{ $template->html_template }}"
                                            data-subject="{{ $template->name }}">
                                        {{ $template->name }} ({{ $template->category }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Channels -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Channels</h5>
                    </div>
                    <div class="card-body">
                        @foreach($channels as $channel)
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="checkbox" name="channels[]"
                                       value="{{ $channel }}" id="channel-{{ $channel }}"
                                       {{ in_array($channel, old('channels', [])) ? 'checked' : '' }}>
                                <label class="form-check-label" for="channel-{{ $channel }}">
                                    @switch($channel)
                                        @case('email')
                                            📧 Email
                                            @break
                                        @case('in_app')
                                            🔔 In-App Notifications
                                            @break
                                        @case('announcement')
                                            📢 Announcement Banner
                                            @break
                                        @case('push')
                                            📱 Push Notifications
                                            @break
                                    @endswitch
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Scheduling -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Scheduling</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="schedule_type" class="form-label">Schedule Type *</label>
                            <select class="form-select @error('schedule_type') is-invalid @enderror"
                                    id="schedule_type" name="schedule_type" required>
                                <option value="immediate" {{ old('schedule_type') === 'immediate' ? 'selected' : '' }}>Send Immediately</option>
                                <option value="scheduled" {{ old('schedule_type') === 'scheduled' ? 'selected' : '' }}>Schedule for Later</option>
                                <option value="recurring" {{ old('schedule_type') === 'recurring' ? 'selected' : '' }}>Recurring</option>
                            </select>
                        </div>

                        <div class="mb-3" id="scheduled_at_group" style="display:none;">
                            <label for="scheduled_at" class="form-label">Schedule Date/Time</label>
                            <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at"
                                   value="{{ old('scheduled_at') }}">
                        </div>

                        <div class="mb-3" id="recurrence_group" style="display:none;">
                            <label for="recurrence_rule" class="form-label">Recurrence Rule</label>
                            <select class="form-select" id="recurrence_rule" name="recurrence_rule">
                                <option value="">-- Select Frequency --</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Announcement Settings -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Announcement Settings (if channel selected)</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="announcement_type" class="form-label">Announcement Type</label>
                            <select class="form-select" id="announcement_type" name="announcement_type">
                                <option value="info">Info</option>
                                <option value="warning">Warning</option>
                                <option value="success">Success</option>
                                <option value="promotion">Promotion</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="announcement_position" class="form-label">Position</label>
                            <select class="form-select" id="announcement_position" name="announcement_position">
                                <option value="top">Top</option>
                                <option value="bottom">Bottom</option>
                                <option value="modal">Modal</option>
                                <option value="sidebar">Sidebar</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Targeting -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Recipient Targeting</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="target_type" class="form-label">Target Audience *</label>
                            <select class="form-select @error('target_type') is-invalid @enderror"
                                    id="target_type" name="target_type" required>
                                <option value="">-- Select Target --</option>
                                @foreach($targetTypes as $value => $label)
                                    <option value="{{ $value }}" {{ old('target_type') === $value ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            @error('target_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div id="segment_select" style="display:none;" class="mb-3">
                            <label for="segment_id" class="form-label">Select Segment</label>
                            <select class="form-select" name="target_config[segment_id]" id="segment_id">
                                <option value="">-- Select Segment --</option>
                                @foreach($segments as $segment)
                                    <option value="{{ $segment->id }}">{{ $segment->name }} ({{ $segment->member_count }} members)</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="tag_input" style="display:none;" class="mb-3">
                            <label for="tag_name" class="form-label">Tag Name</label>
                            <input type="text" class="form-control" name="target_config[tag]" id="tag_name"
                                   placeholder="e.g., premium, early_adopter">
                        </div>

                        <div id="custom_list" style="display:none;" class="mb-3">
                            <label for="user_ids" class="form-label">User IDs (comma-separated)</label>
                            <textarea class="form-control" name="target_config[user_ids]" id="user_ids"
                                      rows="3" placeholder="1, 2, 3, 4, 5"></textarea>
                        </div>

                        <div class="alert alert-info">
                            <strong>Estimated Recipients:</strong>
                            <h4 id="recipient-count">-</h4>
                            <small>This updates as you change targeting options</small>
                        </div>
                    </div>
                </div>

                <!-- Test Email -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Send Test Email</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="test_email" class="form-label">Recipient Email</label>
                            <input type="email" class="form-control" id="test_email"
                                   placeholder="test@example.com">
                        </div>
                        <button type="button" class="btn btn-outline-secondary w-100" id="send-test-btn">
                            <i class="fas fa-paper-plane"></i> Send Test
                        </button>
                    </div>
                </div>

                <!-- Summary -->
                <div class="card">
                    <div class="card-header">
                        <h5>Summary</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Channels:</strong> <span id="summary-channels" class="badge bg-info">None</span></p>
                        <p><strong>Target:</strong> <span id="summary-target" class="badge bg-primary">Not selected</span></p>
                        <p><strong>Schedule:</strong> <span id="summary-schedule" class="badge bg-warning">Immediate</span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 mb-4">
            <button type="submit" class="btn btn-primary btn-lg">
                <i class="fas fa-save"></i> Create Campaign
            </button>
            <a href="{{ route('admin.communications.index') }}" class="btn btn-secondary btn-lg">
                Cancel
            </a>
        </div>
    </form>
</div>

<script>
// Schedule type toggle
document.getElementById('schedule_type').addEventListener('change', function(e) {
    document.getElementById('scheduled_at_group').style.display = e.target.value === 'scheduled' ? 'block' : 'none';
    document.getElementById('recurrence_group').style.display = e.target.value === 'recurring' ? 'block' : 'none';
});

// Target type toggle
document.getElementById('target_type').addEventListener('change', function(e) {
    document.getElementById('segment_select').style.display = e.target.value === 'segment' ? 'block' : 'none';
    document.getElementById('tag_input').style.display = e.target.value === 'tag' ? 'block' : 'none';
    document.getElementById('custom_list').style.display = e.target.value === 'custom_list' ? 'block' : 'none';
    updateRecipientCount();
});

// Update recipient count
function updateRecipientCount() {
    const targetType = document.getElementById('target_type').value;
    if (!targetType) return;

    fetch('/admin/communications/recipient-count', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            target_type: targetType,
            target_config: {
                segment_id: document.getElementById('segment_id').value,
                tag: document.getElementById('tag_name').value,
                user_ids: document.getElementById('user_ids').value ? document.getElementById('user_ids').value.split(',').map(x => x.trim()) : [],
            }
        })
    })
    .then(r => r.json())
    .then(d => {
        if (d.success) {
            document.getElementById('recipient-count').textContent = d.count.toLocaleString();
        }
    });
}

// Template loader
document.getElementById('template-select').addEventListener('change', function(e) {
    if (e.target.value) {
        const option = e.target.options[e.target.selectedIndex];
        document.getElementById('html_content').value = option.dataset.html;
        document.getElementById('subject').value = option.dataset.subject;
    }
});

// Channel summary
document.querySelectorAll('input[name="channels[]"]').forEach(cb => {
    cb.addEventListener('change', updateSummary);
});

function updateSummary() {
    const channels = Array.from(document.querySelectorAll('input[name="channels[]"]:checked')).map(x => x.value).join(', ') || 'None';
    const target = document.getElementById('target_type').options[document.getElementById('target_type').selectedIndex].text || 'Not selected';
    document.getElementById('summary-channels').textContent = channels;
    document.getElementById('summary-target').textContent = target;
}
</script>
@endsection
