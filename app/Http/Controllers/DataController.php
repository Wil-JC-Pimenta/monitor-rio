<?php

namespace App\Http\Controllers;

use App\Models\RiverData;
use App\Models\Station;
use Illuminate\Http\Request;

class DataController extends Controller
{
    public function index(Request $request)
    {
        $query = RiverData::with('station');
        
        // Filtros
        if ($request->filled('station_id')) {
            $query->where('station_id', $request->station_id);
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('data_medicao', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('data_medicao', '<=', $request->date_to);
        }
        
        $data = $query->orderBy('data_medicao', 'desc')->paginate(50);
        $stations = Station::all();
        
        return view('data', compact('data', 'stations'));
    }
}

