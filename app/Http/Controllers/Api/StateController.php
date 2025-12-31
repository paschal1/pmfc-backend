<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\State;
use Illuminate\Http\Request;

class StateController extends Controller
{
    public function index()
    {
        $states = State::orderBy('name', 'asc')->get();
        return response()->json($states);
    }

    public function show(State $state)
    {
        return response()->json($state);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:states,name',
        ]);

        $state = State::create($request->all());

        return response()->json([
            'message' => 'State created successfully.',
            'data' => $state
        ], 201);
    }

    public function update(Request $request, State $state)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:states,name,' . $state->id,
        ]);

        $state->update($request->all());

        return response()->json([
            'message' => 'State updated successfully.',
            'data' => $state
        ]);
    }

    public function destroy(State $state)
    {
        // Check if state is being used in location_costs
        if ($state->locationCosts()->exists()) {
            return response()->json([
                'message' => 'Cannot delete state that is being used in location costs.'
            ], 422);
        }

        $state->delete();

        return response()->json([
            'message' => 'State deleted successfully.'
        ]);
    }
}