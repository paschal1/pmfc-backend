<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    /**
     * Display a listing of all accounts.
     */
    public function index()
    {
        try {
            $accounts = Account::all();
            
            return response()->json([
                'message' => 'Accounts retrieved successfully',
                'data' => $accounts,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve accounts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created account in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'account_type' => 'required|in:bank,paypal,other',
            'account_name' => 'required|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'additional_info' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();

        try {
            // Validate required fields based on account type
            if ($request->input('account_type') === 'bank') {
                $request->validate([
                    'account_number' => 'required|string|max:50',
                    'bank_name' => 'required|string|max:255',
                ]);
            }

            if ($request->input('account_type') === 'paypal') {
                $request->validate([
                    'email' => 'required|email|max:255',
                ]);
            }

            // Create account
            $account = Account::create([
                'account_type' => $request->input('account_type'),
                'account_name' => $request->input('account_name'),
                'account_number' => $request->input('account_number'),
                'bank_name' => $request->input('bank_name'),
                'email' => $request->input('email'),
                'additional_info' => $request->input('additional_info'),
                'is_active' => true,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Account created successfully!',
                'data' => $account,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Account creation failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified account.
     */
    public function show(string $id)
    {
        try {
            $account = Account::findOrFail($id);

            return response()->json([
                'message' => 'Account retrieved successfully',
                'data' => $account,
            ], 200);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Account not found',
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve account',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified account in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'account_type' => 'sometimes|required|in:bank,paypal,other',
            'account_name' => 'sometimes|required|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'bank_name' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'additional_info' => 'nullable|string|max:1000',
            'is_active' => 'nullable|boolean',
        ]);

        DB::beginTransaction();

        try {
            $account = Account::findOrFail($id);

            // Validate required fields based on account type
            $accountType = $request->input('account_type', $account->account_type);
            
            if ($accountType === 'bank') {
                $request->validate([
                    'account_number' => 'required|string|max:50',
                    'bank_name' => 'required|string|max:255',
                ]);
            }

            if ($accountType === 'paypal') {
                $request->validate([
                    'email' => 'required|email|max:255',
                ]);
            }

            // Update account
            $account->update([
                'account_type' => $request->input('account_type', $account->account_type),
                'account_name' => $request->input('account_name', $account->account_name),
                'account_number' => $request->input('account_number', $account->account_number),
                'bank_name' => $request->input('bank_name', $account->bank_name),
                'email' => $request->input('email', $account->email),
                'additional_info' => $request->input('additional_info', $account->additional_info),
                'is_active' => $request->input('is_active', $account->is_active),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Account updated successfully',
                'data' => $account,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Account not found',
            ], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Account update failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Toggle the active status of an account.
     */
    public function toggleStatus(Request $request, string $id)
    {
        $request->validate([
            'is_active' => 'required|boolean',
        ]);

        DB::beginTransaction();

        try {
            $account = Account::findOrFail($id);

            $account->update([
                'is_active' => $request->input('is_active'),
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Account status updated successfully',
                'data' => $account,
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Account not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update account status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified account from storage.
     */
    public function destroy(string $id)
    {
        DB::beginTransaction();

        try {
            $account = Account::findOrFail($id);
            $account->delete();

            DB::commit();

            return response()->json([
                'message' => 'Account deleted successfully',
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Account not found',
            ], 404);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Account deletion failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get only active accounts (for display on checkout page, etc.)
     */
    public function getActiveAccounts()
    {
        try {
            $accounts = Account::active()->get();

            return response()->json([
                'message' => 'Active accounts retrieved successfully',
                'data' => $accounts,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve active accounts',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get accounts by type
     */
    public function getByType(string $type)
    {
        // Validate type
        $validTypes = ['bank', 'paypal', 'other'];
        if (!in_array($type, $validTypes)) {
            return response()->json([
                'message' => 'Invalid account type. Must be: bank, paypal, or other',
            ], 400);
        }

        try {
            $accounts = Account::byType($type)->get();

            return response()->json([
                'message' => "Accounts of type '{$type}' retrieved successfully",
                'data' => $accounts,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve accounts by type',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}