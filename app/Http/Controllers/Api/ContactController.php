<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Mail\ContactMail;
use App\Mail\ContactAdminMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Contact::latest()->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string',
            'phone' => 'required|string',
        ]);
    
        // Capture the contact details from the request
        $details = [
        'name' => $request->input('name'),
        'email' => $request->input('email'),
        'message' => $request->input('message'),
        'phone' => $request->input('phone'),
        ];
       
        
        // Save the contact message to the database
        $contactDetails = Contact::create([
            'name' => $request->name,
            'email' => $request->email,
            'message' => $request->message, // Fixed the typo from 'mesaage' to 'message'
            'phone' => $request->phone,
        ]);
    
        // Send confirmation email to the user
        Mail::to($request->email)->send(new ContactMail($details));
    
        // Send the user's contact message to the admin
        Mail::to('admin@pmfc.com')->send(new ContactAdminMail($details));
    
        return response()->json(['message' => 'Contact message submitted successfully!'], 201);
    }
    
    
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $Contact = Contact::find($id);

        if (!$Contact) {
            return response()->json(['message' => 'Contact not found'], 404);
        }

        return response()->json(['EContact' => $Contact], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $contact = Contact::findOrFail($id);
        $contact->update(['status' => 'reviewed']);

        return response()->json(['message' => 'Contact status updated successfully!']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
