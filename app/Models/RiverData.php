<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiverData extends Model
{
    use HasFactory;

    protected $table = 'river_data';

    protected $fillable = [
        'station_id',
        'nivel',
        'vazao',
        'chuva',
        'data_medicao',
    ];

    protected $casts = [
        'nivel' => 'float',
        'vazao' => 'float',
        'chuva' => 'float',
        'data_medicao' => 'datetime',
    ];

    protected $dates = [
        'data_medicao',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the station that owns the river data.
     */
    public function station(): BelongsTo
    {
        return $this->belongsTo(Station::class, 'station_id', 'id');
    }

    /**
     * Scope a query to only include data from a specific station.
     */
    public function scopeFromStation($query, $stationId)
    {
        return $query->where('station_id', $stationId);
    }

    /**
     * Scope a query to only include recent data.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('data_medicao', '>=', now()->subDays($days));
    }

    /**
     * Scope a query to only include data with critical levels.
     */
    public function scopeCritical($query)
    {
        return $query->where('nivel', '>', 5.0); // Nível crítico > 5m
    }

    /**
     * Get the formatted measurement date.
     */
    public function getFormattedDateAttribute(): string
    {
        return $this->data_medicao->format('d/m/Y H:i');
    }

    /**
     * Get the level status (normal, alert, critical).
     */
    public function getLevelStatusAttribute(): string
    {
        if ($this->nivel === null) return 'unknown';
        
        if ($this->nivel > 5.0) return 'critical';
        if ($this->nivel > 3.0) return 'alert';
        
        return 'normal';
    }

    /**
     * Get the flow status (normal, alert, critical).
     */
    public function getFlowStatusAttribute(): string
    {
        if ($this->vazao === null) return 'unknown';
        
        if ($this->vazao > 1000.0) return 'critical';
        if ($this->vazao > 500.0) return 'alert';
        
        return 'normal';
    }
}
