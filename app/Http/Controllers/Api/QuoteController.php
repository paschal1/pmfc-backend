<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\QuotationMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Quote;
use App\Models\Service;

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
//     public function store(Request $request)
// {
//     $validated = $request->validate([
//         'email' => 'required|email|unique:quotes,email',
//         'services' => 'required|array',
//         'services.*' => 'exists:services,id', // Validate each service ID
//     ]);

//     // Fetch selected services and their prices
//     $services = Service::whereIn('id', $validated['services'])->get();

//     // Generate the quotation details
//     $quoteData = $services->map(function ($service) {
//         return [
//             'id' => $service->id,
//             'title' => $service->title,
//             'price' => $service->price,
//         ];
//     });

//     $total = $services->sum('price');

//     // Now save the quote to the database
//     $quote = Quote::create([
//         'email' => $validated['email'],
//         'service_ids' => json_encode($validated['services']),  // Save service IDs as JSON
//         'service_titles' => implode(', ', $services->pluck('title')->toArray()),  // Save titles as CSV
//         'service_prices' => implode(', ', $services->pluck('price')->toArray()),  // Save prices as CSV
//         'details' => $quoteData->toJson(),  // Save quote details as JSON
//         'quote' => json_encode(['services' => $quoteData, 'total' => $total]),  // Save full quote
//     ]);

//     // Debug the saved quote to ensure the data is correct
//     //dd($quote); // Use this to inspect the saved quote data

//     // Send the quote via email
//     Mail::to($validated['email'])->send(new QuotationMail($quote));

//     // Mark as sent
//     $quote->update(['status' => 'sent']);

//     return response()->json(['message' => 'Quotation sent successfully!'], 201);
// }

public function store(Request $request)
{
    // Validate the incoming request
    $validated = $request->validate([
        'email' => 'required|email|unique:quotes,email',
        'name' => 'required|string',
        'phone' => 'required|string',
        'message' => 'required|string',
        'areasize' => 'required|string',
        'location' => 'required|string',
        'squarefeet' => 'required|string',
        'budget' => 'required|string',
        'services' => 'required|array',
        'services.*' => 'exists:services,id', // Validate each service ID
    ]);

    // Fetch the selected services and their prices
    $services = Service::whereIn('id', $validated['services'])->get();

    // Prepare the details and total data for the quotation
    $quoteData = [];
    $total = 0;

    foreach ($services as $service) {
        $quoteData[] = [
            'id' => $service->id,
            'title' => $service->title,
            'price' => $service->price,
        ];
        $total += $service->price; // Add to total sum
    }

    // Prepare the data for the quote
    $serviceIds = $validated['services'];  // Service IDs
    $serviceTitles = $services->pluck('title')->toArray();  // Service Titles as array
    $servicePrices = $services->pluck('price')->toArray();  // Service Prices as array

    // Save the quote to the database
    $quote = new Quote();
    $quote->email = $validated['email'];
    $quote->service_ids = json_encode($serviceIds);  // Save service IDs as JSON
    $quote->service_titles = implode(', ', $serviceTitles);  // Save titles as CSV
    $quote->service_prices = implode(', ', $servicePrices);  // Save prices as CSV
    $quote->details = json_encode($quoteData);  // Save quote details as JSON
    $quote->quote = json_encode(['services' => $quoteData, 'total' => $total]);  // Save full quote
    $quote->status = 'pending';  // Default status as 'pending'
    $quote->save();  // Save the quote to the database

    // Debug the saved quote to check if data is correct
    // dd($quote);  // Uncomment to inspect saved data

    // Send the quote via email
    Mail::to($validated['email'])->send(new QuotationMail($quote));

    // Mark as sent
    $quote->update(['status' => 'sent']);

    return response()->json(['message' => 'Quotation sent successfully!'], 201);
}


}
