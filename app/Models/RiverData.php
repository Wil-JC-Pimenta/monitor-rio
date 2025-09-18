<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RiverData extends Model
{
    use HasFactory;

    // Campos que podem ser preenchidos em massa
    protected $fillable = [
        'station_id',
        'nivel',
        'vazao',
        'chuva',
        'data_medicao',
    ];

    // Laravel vai tratar esse campo como objeto Carbon (data/hora)
    protected $dates = ['data_medicao'];

    // Relacionamento com Station
    public function station()
    {
        return $this->belongsTo(Station::class);
    }
}
