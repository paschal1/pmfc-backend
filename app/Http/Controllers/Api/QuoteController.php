<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\QuotationMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Quote;
use App\Models\Service;
use App\Models\LocationCost;
use App\Models\State;

class QuoteController extends Controller
{
    // List all quotes
    public function index()
    {
        $quotes = Quote::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => 'success',
            'data' => $quotes
        ], 200);
    }

    // Show a specific quote
    public function show($id)
    {
        $quote = Quote::find($id);

        if (!$quote) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quote not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $quote
        ], 200);
    }

    // Delete a quote (admin only)
    public function destroy($id)
    {
        $quote = Quote::find($id);

        if (!$quote) {
            return response()->json([
                'status' => 'error',
                'message' => 'Quote not found'
            ], 404);
        }

        $quote->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Quote deleted successfully'
        ], 200);
    }

    public function store(Request $request)
    {
        // Validate the incoming request
        $validated = $request->validate([
            'email' => 'required|email|unique:quotes,email',
            'name' => 'required|string',
            'phone' => 'required|string',
            'message' => 'required|string',
            'areasize' => 'required|numeric|min:1',
            'squarefeet' => 'required|numeric|min:1',
            'state_id' => 'required|exists:states,id',
            'location' => 'required|string',
            'budget' => 'required|string',
            'services' => 'required|array',
            'services.*' => 'exists:services,id',
        ]);

        // Get location cost for selected state
        $locationCost = LocationCost::where('state_id', $validated['state_id'])->first();
        if (!$locationCost) {
            return response()->json([
                'status' => 'error',
                'message' => 'Location cost not set for selected state'
            ], 422);
        }

        // Fetch the selected services and their prices
        $services = Service::whereIn('id', $validated['services'])->get();

        $quoteData = [];
        $totalPrice = 0;

        foreach ($services as $service) {
            // Calculate average price from min and max price
            $basePrice = ($service->min_price + $service->max_price) / 2;

            // Calculate estimated price based on area size and square feet
            $estimatedPrice = $basePrice * $validated['areasize'] * $validated['squarefeet'];

            // Add flat location cost
            $finalPrice = $estimatedPrice + $locationCost->cost;

            $finalPrice = round($finalPrice, 2);

            $quoteData[] = [
                'id' => $service->id,
                'title' => $service->title,
                'min_price' => $service->min_price,
                'max_price' => $service->max_price,
                'main_price' => $service->price,
                'estimated_price' => $finalPrice,
            ];

            $totalPrice += $finalPrice;
        }

        // Prepare arrays to save
        $serviceIds = $services->pluck('id')->toArray();
        $serviceTitles = $services->pluck('title')->toArray();
        $servicePrices = array_map(function ($item) {
            return $item['estimated_price'];
        }, $quoteData);

        // Save the quote
        $quote = new Quote();
        $quote->email = $validated['email'];
        $quote->name = $validated['name'];
        $quote->phone = $validated['phone'];
        $quote->message = $validated['message'];
        $quote->areasize = $validated['areasize'];
        $quote->squarefeet = $validated['squarefeet'];
        $quote->state_id = $validated['state_id'];
        $quote->address = $validated['location'];
        $quote->budget = $validated['budget'];
        $quote->service_ids = json_encode($serviceIds);
        $quote->service_titles = implode(', ', $serviceTitles);
        $quote->service_prices = implode(', ', $servicePrices);
        $quote->details = json_encode($quoteData);
        $quote->quote = json_encode(['services' => $quoteData, 'total' => round($totalPrice, 2)]);
        $quote->total_price = round($totalPrice, 2);
        $quote->status = 'pending';
        $quote->save();

        // Send the quote via email
        Mail::to($validated['email'])->send(new QuotationMail($quote));

        // Update status to sent
        $quote->update(['status' => 'sent']);

        return response()->json([
            'message' => 'Quotation sent successfully!',
            'data' => $quote
        ], 201);
    }
}
