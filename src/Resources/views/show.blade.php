@extends('logbook::layout')

@section('title', 'Entry Details - Laravel Logbook')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-eye"></i> Entry Details</h1>
    <a href="{{ route('logbook.tracks') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left"></i> Back to Tracks
    </a>
</div>

<div class="row">
    <!-- General Information -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-info-circle"></i> General Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>ID</th>
                        <td>{{ $entry->id }}</td>
                    </tr>
                    <tr>
                        <th>Type</th>
                        <td>
                            <span class="badge bg-{{ $entry->type === 'request' ? 'primary' : 'info' }}">
                                {{ ucfirst($entry->type) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>Timestamp</th>
                        <td>{{ $entry->created_at->format('Y-m-d H:i:s') ?? 'N/A' }}</td>
                    </tr>
                    @if($entry->type === 'request')
                        <tr>
                            <th>Method</th>
                            <td>
                                <span class="badge method-{{ strtolower($entry->method) }} text-white">
                                    {{ $entry->method }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Status Code</th>
                            <td>
                                <span class="badge bg-{{ $entry->status_color === 'success' ? 'success' : ($entry->status_color === 'danger' ? 'danger' : 'warning') }}">
                                    {{ $entry->status_code }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Response Time</th>
                            <td>{{ $entry->formatted_response_time }}</td>
                        </tr>
                        <tr>
                            <th>Endpoint</th>
                            <td><code>{{ $entry->endpoint }}</code></td>
                        </tr>
                        <tr>
                            <th>Full URL</th>
                            <td class="text-break">{{ $entry->url }}</td>
                        </tr>
                    @else
                        <tr>
                            <th>Event Name</th>
                            <td><span class="text-info">{{ $entry->event_name }}</span></td>
                        </tr>
                    @endif
                    <tr>
                        <th>IP Address</th>
                        <td>{{ $entry->ip_address ?: 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>User ID</th>
                        <td>{{ $entry->user_id ? "User #{$entry->user_id}" : 'Guest' }}</td>
                    </tr>
                    @if($entry->token_id)
                        <tr>
                            <th>Token ID</th>
                            <td><code>{{ $entry->token_id }}</code></td>
                        </tr>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- User Agent & Metadata -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-desktop"></i> Client Information</h5>
            </div>
            <div class="card-body">
                <h6>User Agent</h6>
                <p class="text-break small">{{ $entry->user_agent ?: 'N/A' }}</p>

                @if($entry->metadata)
                    <h6>Metadata</h6>
                    <pre class="bg-light p-2 rounded"><code>{{ json_encode($entry->metadata, JSON_PRETTY_PRINT) }}</code></pre>
                @endif
            </div>
        </div>
    </div>
</div>

@if($entry->type === 'request')
    <!-- Request/Response Headers -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-arrow-up"></i> Request Headers</h5>
                </div>
                <div class="card-body">
                    @if($entry->request_headers)
                        <pre class="bg-light p-2 rounded small"><code>{{ json_encode($entry->request_headers, JSON_PRETTY_PRINT) }}</code></pre>
                    @else
                        <p class="text-muted">No request headers recorded</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-arrow-down"></i> Response Headers</h5>
                </div>
                <div class="card-body">
                    @if($entry->response_headers)
                        <pre class="bg-light p-2 rounded small"><code>{{ json_encode($entry->response_headers, JSON_PRETTY_PRINT) }}</code></pre>
                    @else
                        <p class="text-muted">No response headers recorded</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Request/Response Bodies -->
    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-code"></i> Request Body</h5>
                </div>
                <div class="card-body">
                    @if($entry->request_body)
                        <pre class="bg-light p-2 rounded small" style="max-height: 400px; overflow-y: auto;"><code>{{ $entry->request_body }}</code></pre>
                    @else
                        <p class="text-muted">No request body</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5><i class="fas fa-code"></i> Response Body</h5>
                </div>
                <div class="card-body">
                    @if($entry->response_body)
                        <pre class="bg-light p-2 rounded small" style="max-height: 400px; overflow-y: auto;"><code>{{ $entry->response_body }}</code></pre>
                    @else
                        <p class="text-muted">No response body</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@else
    <!-- Event Data -->
    <div class="card mb-4">
        <div class="card-header">
            <h5><i class="fas fa-database"></i> Event Data</h5>
        </div>
        <div class="card-body">
            @if($entry->event_data)
                <pre class="bg-light p-2 rounded"><code>{{ json_encode($entry->event_data, JSON_PRETTY_PRINT) }}</code></pre>
            @else
                <p class="text-muted">No event data</p>
            @endif
        </div>
    </div>
@endif
@endsection