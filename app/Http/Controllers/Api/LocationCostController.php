<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LocationCost;
use App\Models\State;
use Illuminate\Http\Request;

class LocationCostController extends Controller
{
    public function index()
    {
        $costs = LocationCost::with('state')->get();
        return response()->json($costs);
    }

    public function store(Request $request)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id|unique:location_costs,state_id',
            'cost' => 'required|numeric|min:0',
        ]);

        $locationCost = LocationCost::create($request->all());

        return response()->json([
            'message' => 'Location cost added successfully.',
            'data' => $locationCost
        ], 201);
    }

    public function show(LocationCost $locationCost)
    {
        $locationCost->load('state');
        return response()->json($locationCost);
    }

    public function update(Request $request, LocationCost $locationCost)
    {
        $request->validate([
            'state_id' => 'required|exists:states,id|unique:location_costs,state_id,' . $locationCost->id,
            'cost' => 'required|numeric|min:0',
        ]);

        $locationCost->update($request->all());

        return response()->json([
            'message' => 'Location cost updated successfully.',
            'data' => $locationCost
        ]);
    }

    public function destroy(LocationCost $locationCost)
    {
        $locationCost->delete();

        return response()->json([
            'message' => 'Location cost deleted successfully.'
        ]);
    }
}
