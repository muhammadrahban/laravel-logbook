@extends('logbook::layout')

@section('title', 'Request Tracks - Laravel Logbook')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-list"></i> Request Tracks</h1>
    <div class="text-muted">Total: {{ $entries->total() }} entries</div>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-header">
        <h6><i class="fas fa-filter"></i> Filters</h6>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('logbook.tracks') }}">
            <div class="row">
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="request" {{ request('type') === 'request' ? 'selected' : '' }}>Requests</option>
                        <option value="event" {{ request('type') === 'event' ? 'selected' : '' }}>Events</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Method</label>
                    <select name="method" class="form-select">
                        <option value="">All Methods</option>
                        @foreach($methods as $method)
                            <option value="{{ $method }}" {{ request('method') === $method ? 'selected' : '' }}>
                                {{ $method }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        @foreach($statusCodes as $status)
                            <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">User ID</label>
                    <input type="number" name="user_id" class="form-control" value="{{ request('user_id') }}" placeholder="User ID">
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from" class="form-control" value="{{ request('from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to" class="form-control" value="{{ request('to') }}">
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label class="form-label">Endpoint</label>
                    <input type="text" name="endpoint" class="form-control" value="{{ request('endpoint') }}" placeholder="Search endpoints...">
                </div>
                <div class="col-md-6 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2">
                        <i class="fas fa-search"></i> Filter
                    </button>
                    <a href="{{ route('logbook.tracks') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Results -->
<div class="card">
    <div class="card-body p-0">
        @if($entries->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Method</th>
                            <th>Endpoint/Event</th>
                            <th>Status</th>
                            <th>Response Time</th>
                            <th>User</th>
                            <th>IP Address</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($entries as $entry)
                            <tr>
                                <td>
                                    <small class="text-muted">{{ $entry->created_at->format('Y-m-d') }}</small><br>
                                    <strong>{{ $entry->created_at->format('H:i:s') }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $entry->type === 'request' ? 'primary' : 'info' }}">
                                        {{ ucfirst($entry->type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($entry->method)
                                        <span class="badge method-{{ strtolower($entry->method) }} text-white">
                                            {{ $entry->method }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 300px;">
                                        @if($entry->type === 'request')
                                            <span title="{{ $entry->url }}">{{ $entry->endpoint ?: 'Unknown' }}</span>
                                        @else
                                            <span class="text-info">{{ $entry->event_name }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($entry->status_code)
                                        <span class="badge bg-{{ $entry->status_color === 'success' ? 'success' : ($entry->status_color === 'danger' ? 'danger' : 'warning') }}">
                                            {{ $entry->status_code }}
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $entry->formatted_response_time }}
                                </td>
                                <td>
                                    @if($entry->user_id)
                                        <span class="badge bg-secondary">User #{{ $entry->user_id }}</span>
                                    @else
                                        <span class="text-muted">Guest</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">{{ $entry->ip_address ?: '-' }}</small>
                                </td>
                                <td>
                                    <a href="{{ route('logbook.show', $entry->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center p-3">
                <div class="text-muted">
                    Showing {{ $entries->firstItem() ?? 0 }} to {{ $entries->lastItem() ?? 0 }} of {{ $entries->total() }} results
                </div>
                {{ $entries->links() }}
            </div>
        @else
            <div class="text-center p-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No entries found</h5>
                <p class="text-muted">Try adjusting your filters or check back later.</p>
            </div>
        @endif
    </div>
</div>
@endsection