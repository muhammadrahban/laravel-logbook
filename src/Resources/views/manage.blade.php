@extends('logbook::layout')

@section('title', 'Management - Laravel Logbook')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1><i class="fas fa-cogs"></i> Management</h1>
</div>

<!-- Storage Overview -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ number_format($totalEntries) }}</h4>
                        <p class="mb-0">Total Entries</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-database fa-2x"></i>
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
                        <h4>{{ number_format($oldEntries) }}</h4>
                        <p class="mb-0">Old Entries ({{ $retentionDays }}+ days)</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-clock fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-secondary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ number_format($totalSizeKb) }} KB</h4>
                        <p class="mb-0">Est. Storage Size</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-hdd fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4>{{ $retentionDays }}</h4>
                        <p class="mb-0">Retention Days</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-calendar fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-chart-line"></i> Quick Statistics</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 text-center">
                        <h4 class="text-primary">{{ number_format($stats['total_requests']) }}</h4>
                        <p class="text-muted">Total Requests</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-info">{{ number_format($stats['total_events']) }}</h4>
                        <p class="text-muted">Custom Events</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-warning">{{ $stats['error_rate'] }}%</h4>
                        <p class="text-muted">Error Rate</p>
                    </div>
                    <div class="col-md-3 text-center">
                        <h4 class="text-success">{{ $stats['avg_response_time'] }}ms</h4>
                        <p class="text-muted">Avg Response Time</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cleanup Operations -->
<div class="card">
    <div class="card-header">
        <h5><i class="fas fa-trash-alt"></i> Cleanup Operations</h5>
    </div>
    <div class="card-body">
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Warning:</strong> Cleanup operations cannot be undone. Please ensure you have backups if needed.
        </div>

        <div class="row">
            <!-- Cleanup by Days -->
            <div class="col-md-4 mb-3">
                <div class="card border">
                    <div class="card-header">
                        <h6>Cleanup by Days</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted">Remove entries older than specified days</p>
                        <div class="mb-3">
                            <label class="form-label">Days</label>
                            <input type="number" class="form-control" id="cleanup-days" value="{{ $retentionDays }}" min="1">
                        </div>
                        <button class="btn btn-warning btn-sm w-100" onclick="cleanupByDays()">
                            <i class="fas fa-calendar-times"></i> Cleanup Old Entries
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cleanup by Date Range -->
            <div class="col-md-4 mb-3">
                <div class="card border">
                    <div class="card-header">
                        <h6>Cleanup by Date Range</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted">Remove entries within a specific date range</p>
                        <div class="mb-2">
                            <label class="form-label">From</label>
                            <input type="date" class="form-control" id="cleanup-from">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">To</label>
                            <input type="date" class="form-control" id="cleanup-to">
                        </div>
                        <button class="btn btn-warning btn-sm w-100" onclick="cleanupByRange()">
                            <i class="fas fa-calendar-alt"></i> Cleanup Range
                        </button>
                    </div>
                </div>
            </div>

            <!-- Cleanup All -->
            <div class="col-md-4 mb-3">
                <div class="card border border-danger">
                    <div class="card-header bg-danger text-white">
                        <h6>Cleanup All Data</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted">Remove ALL logbook entries</p>
                        <div class="alert alert-danger small">
                            This will delete all {{ number_format($totalEntries) }} entries permanently!
                        </div>
                        <button class="btn btn-danger btn-sm w-100" onclick="cleanupAll()">
                            <i class="fas fa-trash"></i> Delete All Entries
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Results Modal -->
<div class="modal fade" id="resultModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="resultModalTitle">Operation Result</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resultModalBody">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="location.reload()">Refresh Page</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Set CSRF token for AJAX requests
    const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    window.cleanupByDays = function() {
        const days = document.getElementById('cleanup-days').value;
        if (!days || days < 1) {
            alert('Please enter a valid number of days');
            return;
        }
        
        if (!confirm(`Are you sure you want to delete all entries older than ${days} days?`)) {
            return;
        }
        
        fetch(`{{ route('logbook.manage.cleanup.days', '') }}/${days}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => showResult(data))
        .catch(error => showError(error));
    };
    
    window.cleanupByRange = function() {
        const from = document.getElementById('cleanup-from').value;
        const to = document.getElementById('cleanup-to').value;
        
        if (!from || !to) {
            alert('Please select both from and to dates');
            return;
        }
        
        if (!confirm(`Are you sure you want to delete all entries between ${from} and ${to}?`)) {
            return;
        }
        
        fetch('{{ route('logbook.manage.cleanup.range') }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ from, to })
        })
        .then(response => response.json())
        .then(data => showResult(data))
        .catch(error => showError(error));
    };
    
    window.cleanupAll = function() {
        if (!confirm('Are you ABSOLUTELY sure you want to delete ALL logbook entries?')) {
            return;
        }
        
        if (!confirm('This action cannot be undone. Are you sure?')) {
            return;
        }
        
        fetch('{{ route('logbook.manage.cleanup.all') }}', {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': token,
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => showResult(data))
        .catch(error => showError(error));
    };
    
    function showResult(data) {
        document.getElementById('resultModalTitle').textContent = 'Cleanup Successful';
        document.getElementById('resultModalBody').innerHTML = `
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> ${data.message}
            </div>
        `;
        new bootstrap.Modal(document.getElementById('resultModal')).show();
    }
    
    function showError(error) {
        document.getElementById('resultModalTitle').textContent = 'Error';
        document.getElementById('resultModalBody').innerHTML = `
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> An error occurred: ${error.message || 'Unknown error'}
            </div>
        `;
        new bootstrap.Modal(document.getElementById('resultModal')).show();
    }
});
</script>
@endpush