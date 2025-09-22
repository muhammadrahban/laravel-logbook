@extends('logbook::layout')

@section('title', 'Dashboard - Laravel Logbook')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-tachometer-alt"></i> Dashboard</h1>
    <div class="text-muted">Last updated: {{ now()->format('Y-m-d H:i:s') }}</div>
</div>

<!-- Stats Cards -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ number_format($stats['total_requests']) }}</h4>
                        <p class="mb-0">Total Requests</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-globe fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ number_format($stats['total_events']) }}</h4>
                        <p class="mb-0">Custom Events</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-bell fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $stats['error_rate'] }}%</h4>
                        <p class="mb-0">Error Rate</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-exclamation-triangle fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $stats['avg_response_time'] }}ms</h4>
                        <p class="mb-0">Avg Response Time</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Status Code Distribution -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-pie"></i> Status Code Distribution</h5>
            </div>
            <div class="card-body">
                @if($statusCodes->count() > 0)
                    @foreach($statusCodes as $status)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="status-{{ strtolower($status->status_group) }}">
                                <strong>{{ $status->status_group }}</strong>
                            </span>
                            <span class="badge bg-secondary">{{ $status->count }}</span>
                        </div>
                        <div class="progress mb-3" style="height: 8px;">
                            <div class="progress-bar bg-{{ $status->status_group === '2xx' ? 'success' : ($status->status_group === '4xx' || $status->status_group === '5xx' ? 'danger' : 'warning') }}" 
                                 style="width: {{ ($status->count / $stats['total_requests']) * 100 }}%"></div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No request data available</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Top Endpoints -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-bar"></i> Top Endpoints</h5>
            </div>
            <div class="card-body">
                @if($stats['top_endpoints']->count() > 0)
                    @foreach($stats['top_endpoints'] as $endpoint)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-truncate" style="max-width: 200px;" title="{{ $endpoint->endpoint }}">
                                {{ $endpoint->endpoint ?: 'Unknown' }}
                            </span>
                            <span class="badge bg-primary">{{ $endpoint->count }}</span>
                        </div>
                        <div class="progress mb-3" style="height: 6px;">
                            <div class="progress-bar" 
                                 style="width: {{ ($endpoint->count / $stats['top_endpoints']->first()->count) * 100 }}%"></div>
                        </div>
                    @endforeach
                @else
                    <p class="text-muted">No endpoint data available</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Recent Activity -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-clock"></i> Recent Activity (Last 24 Hours)</h5>
    </div>
    <div class="card-body">
        @if($recentEntries->count() > 0)
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Method</th>
                            <th>Endpoint</th>
                            <th>Status</th>
                            <th>Response Time</th>
                            <th>User</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentEntries as $entry)
                            <tr>
                                <td>{{ $entry->created_at->format('H:i:s') }}</td>
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
                                    @endif
                                </td>
                                <td class="text-truncate" style="max-width: 200px;">
                                    <a href="{{ route('logbook.show', $entry->id) }}" class="text-decoration-none">
                                        {{ $entry->endpoint ?: $entry->event_name ?: 'N/A' }}
                                    </a>
                                </td>
                                <td>
                                    @if($entry->status_code)
                                        <span class="status-{{ $entry->status_color }}">
                                            {{ $entry->status_code }}
                                        </span>
                                    @endif
                                </td>
                                <td>{{ $entry->formatted_response_time }}</td>
                                <td>{{ $entry->user_id ? "User #{$entry->user_id}" : '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="text-center mt-3">
                <a href="{{ route('logbook.tracks') }}" class="btn btn-outline-primary">
                    View All Tracks <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        @else
            <p class="text-muted">No recent activity</p>
        @endif
    </div>
</div>
@endsection