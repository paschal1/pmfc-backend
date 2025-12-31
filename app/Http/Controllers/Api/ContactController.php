<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Contact;
use App\Mail\ContactMail;
use App\Mail\ContactAdminMail;
use App\Mail\ContactReplyMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ContactController extends Controller
{
    /**
     * Get count of pending messages
     */
    public function getPendingCount()
    {
        try {
            $pendingCount = Contact::where('status', 'pending')->count();
            return response()->json([
                'pending_count' => $pendingCount,
                'message' => 'Pending messages count retrieved successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve pending count',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get statistics about contacts
     */
    public function getStats()
    {
        try {
            $stats = [
                'total_contacts' => Contact::count(),
                'pending_count' => Contact::where('status', 'pending')->count(),
                'reviewed_count' => Contact::where('status', 'reviewed')->count(),
                'responded_count' => Contact::where('status', 'responded')->count(),
            ];
            return response()->json($stats, 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to retrieve statistics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch the latest contacts from the database
        // You can modify the query to include pagination or filtering as needed
        $contacts = Contact::latest()->paginate(10);
        return $this->respondWithData($contacts);
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
            'message' => $request->message,
            'phone' => $request->phone,
            'status' => 'pending', // Set initial status to pending
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

        return $this->respondWithData($Contact);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $contact = Contact::findOrFail($id);
        
        // Allow updating status
        if ($request->has('status')) {
            $request->validate([
                'status' => 'in:pending,reviewed,responded'
            ]);
            $contact->update(['status' => $request->status]);
        }

        return response()->json(['message' => 'Contact status updated successfully!']);
    }

    /**
     * Send a reply to a contact message
     * Sends email to user and copy to admin
     * Updates contact status to 'responded'
     */
    public function sendReply(Request $request, string $id)
    {
        try {
            $contact = Contact::findOrFail($id);

            // Validate reply message
            $validated = $request->validate([
                'reply_message' => 'required|string|min:1',
            ]);

            $replyMessage = $request->input('reply_message');

            // Prepare data for email
            $replyData = [
                'contact_name' => $contact->name,
                'contact_email' => $contact->email,
                'contact_phone' => $contact->phone,
                'original_message' => $contact->message,
                'reply_message' => $replyMessage,
                'sent_at' => now()->format('M d, Y H:i A'),
            ];

            // Send reply email to user
            Mail::to($contact->email)->send(new ContactReplyMail($replyData));

            // Send copy of reply to admin
            Mail::to('admin@pmfc.com')->send(new ContactReplyMail($replyData, isAdmin: true));

            // Update contact status to responded
            $contact->update(['status' => 'responded']);

            return response()->json([
                'message' => 'Reply sent successfully to ' . $contact->email,
                'contact_id' => $contact->id,
                'status' => 'responded',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Contact not found',
                'message' => 'The contact you are trying to reply to does not exist'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to send reply',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $contact = Contact::findOrFail($id);
            $contact->delete();
            
            return response()->json(['message' => 'Contact deleted successfully!'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to delete contact',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}