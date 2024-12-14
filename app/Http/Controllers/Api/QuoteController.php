<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\QuotationMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Qoute;

class QuoteController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'services' => 'required|array',
            'services.*' => 'exists:services,id', // Validate each service ID
        ]);

        // Fetch selected services and their prices
        $services = Service::whereIn('id', $validated['services'])->get();

        // Generate the quotation
        $quoteData = $services->map(function ($service) {
            return "{$service->name}: {$service->price}";
        })->implode("\n");

        $total = $services->sum('price');
        $quoteData .= "\n\nTotal: $total";

        // Save the quote to the database
        $quote = Quote::create([
            'email' => $validated['email'],
            'details' => json_encode($services), // Save selected services as JSON
            'quote' => $quoteData,
        ]);

        // Send the quote via email
        Mail::to($validated['email'])->send(new QuotationMail($quoteData));

        // Mark as sent
        $quote->update(['status' => 'sent']);

        return response()->json(['message' => 'Quotation sent successfully!'], 201);
    }
}
