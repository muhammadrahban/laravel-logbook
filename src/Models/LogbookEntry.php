<?php

namespace Rahban\LaravelLogbook\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LogbookEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'method',
        'url',
        'endpoint',
        'status_code',
        'response_time',
        'ip_address',
        'user_agent',
        'user_id',
        'token_id',
        'request_headers',
        'response_headers',
        'request_body',
        'response_body',
        'event_name',
        'event_data',
        'metadata',
    ];

    protected $casts = [
        'request_headers' => 'array',
        'response_headers' => 'array',
        'event_data' => 'array',
        'metadata' => 'array',
        'response_time' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Only set created_at if it's not already set (for factories/tests)
            if (!$model->created_at) {
                $model->created_at = now();
            }
        });
    }

    protected static function newFactory()
    {
        return \Rahban\LaravelLogbook\Database\Factories\LogbookEntryFactory::new();
    }

    public function getConnectionName()
    {
        return config('logbook.database_connection') ?: parent::getConnectionName();
    }

    // Scopes
    public function scopeRequests(Builder $query): Builder
    {
        return $query->where('type', 'request');
    }

    public function scopeEvents(Builder $query): Builder
    {
        return $query->where('type', 'event');
    }

    public function scopeByMethod(Builder $query, string $method): Builder
    {
        return $query->where('method', $method);
    }

    public function scopeByStatus(Builder $query, int $status): Builder
    {
        return $query->where('status_code', $status);
    }

    public function scopeByUser(Builder $query, int $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByEndpoint(Builder $query, string $endpoint): Builder
    {
        return $query->where('endpoint', 'like', "%{$endpoint}%");
    }

    public function scopeDateRange(Builder $query, $from, $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to]);
    }

    public function scopeOlderThan(Builder $query, int $days): Builder
    {
        return $query->where('created_at', '<', now()->subDays($days));
    }

    // Accessors
    public function getStatusColorAttribute(): string
    {
        if ($this->status_code >= 200 && $this->status_code < 300) {
            return 'success';
        } elseif ($this->status_code >= 300 && $this->status_code < 400) {
            return 'warning';
        } else {
            return 'danger';
        }
    }

    public function getFormattedResponseTimeAttribute(): string
    {
        if ($this->response_time === null) {
            return 'N/A';
        }
        return number_format($this->response_time, 2) . 'ms';
    }

    public function getTruncatedUrlAttribute(): string
    {
        return strlen($this->url) > 50 ? substr($this->url, 0, 50) . '...' : $this->url;
    }
}
