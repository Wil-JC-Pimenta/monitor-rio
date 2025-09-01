<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Station extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'latitude',
        'longitude',
        'description',
        'status',
        'last_measurement',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
        'last_measurement' => 'datetime',
    ];

    protected $dates = [
        'last_measurement',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the river data for this station.
     */
    public function riverData(): HasMany
    {
        return $this->hasMany(RiverData::class, 'station_id', 'id');
    }

    /**
     * Get the latest river data for this station.
     */
    public function latestRiverData()
    {
        return $this->riverData()->latest('data_medicao')->first();
    }

    /**
     * Scope a query to only include active stations.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include stations with recent data.
     */
    public function scopeWithRecentData($query, $hours = 24)
    {
        return $query->where('last_measurement', '>=', now()->subHours($hours));
    }

    /**
     * Get the formatted location.
     */
    public function getFormattedLocationAttribute(): string
    {
        if ($this->latitude && $this->longitude) {
            return "{$this->latitude}, {$this->longitude}";
        }
        
        return $this->location ?? 'Localização não informada';
    }

    /**
     * Get the status badge color.
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'active' => 'green',
            'inactive' => 'red',
            'maintenance' => 'yellow',
            default => 'gray',
        };
    }

    /**
     * Check if station is online (has recent data).
     */
    public function isOnline(): bool
    {
        if (!$this->last_measurement) {
            return false;
        }
        
        return $this->last_measurement->isAfter(now()->subHours(6));
    }
}
