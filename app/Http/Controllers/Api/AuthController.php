<?php

namespace App\Http\Controllers\Api;



use App\Models\Otp;
use App\Models\User;
use App\Models\Cart;
use App\Utility\Strings;
use App\Utility\UserActivityService;
use App\Notifications\SendOtp;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Traits\ApiResponseTrait; // Include the trait
use Illuminate\Http\JsonResponse;


class AuthController extends Controller
{

            protected function sendError($message, $errors = [], $code = 400)
        {
            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ], $code);
        }

        public function sendResponse(string $message, $data = []): JsonResponse
        {
            $response = [
                'success' => true,
                'message' => $message,
                'data' => $data,
            ];
        
            return response()->json($response, 200);
        }
                //
                public function login(Request $request)
                {
                    $request->validate([
                        'email' => 'required|string|email',
                        'password' => 'required|string',
                        'remember_me' => 'nullable|boolean',
                    ]);
                
                    $remember = filter_var($request->remember_me, FILTER_VALIDATE_BOOLEAN);
                
                    if (Auth::guard('web')->attempt(['email' => $request->email, 'password' => $request->password], $remember)) {
                        $user = Auth::user();
                
                        // Update device data and last login
                        $user->device_token = $request->device_token ?? $user->device_token;
                        $user->device_platform = $request->device_platform ?? $user->device_platform;
                        $user->last_login_at = now();
                        $user->save();
                
                        // Log user activity
                        UserActivityService::log(
                            $user->id,
                            'login',
                            $request->header('User-Agent'),
                            $request->ip()
                        );
                
                        // Generate API token
                        $bearerToken = $this->getApiToken($user);
                
                        // Check or create cart
                        $cart = Cart::firstOrCreate(
                            ['user_id' => $user->id, 'status' => 'active'],
                            ['status' => 'active']
                        );
                
                        return response()->json([
                            'message' => 'Logged in successfully',
                            'user' => $user, // optionally wrap with UserResource
                            'bearer_token' => $bearerToken,
                            'cart' => $cart,
                        ]);
                    }
                
                    return response()->json([
                        'message' => 'Invalid login credentials.',
                    ], 422);
                }
                
                
    
        public function getApiToken(User $user)
        {
            if ($user->tokens()) {
                $user->tokens()->delete();
            }
            return $user->createToken($user->email)->plainTextToken;
        }
    
        public function send_otp(Request $request)
        {
            $user_otp = Otp::where('email',$request->email)->first();
            $user = User::where('email',$request->email)->first();
            if ($user) {
                $otp = rand(10000, 99999);
                if (!empty($user_otp->otp)) {
                    $user_otp->email = $request->email;
                    $user_otp->otp = $otp;
                    $user_otp->update();
                } else {
                    Otp::create([
                        'email' => $request->email,
                        'otp' => $otp
                    ]);
                }
    
                $user->notify(new SendOtp($user, $otp));
                return $this->sendResponse(
                    'OTP Code sent successfully',
                    [
                        'email' => $user->email
                    ]
                );
            } else {
                return $this->sendError(Strings::NetworkError(), [], 500);
            }
        }
    
        
        public function resend_otp(Request $request)
        {
            $user_otp = Otp::where('email',$request->email)->first();
            $user = User::where('email',$request->email)->first();
            if ($user) {
                $otp = rand(10000, 99999);
                if (!empty($user_otp->otp)) {
                    $user_otp->email = $request->email;
                    $user_otp->otp = $otp;
                    $user_otp->update();
                } else {
                    Otp::create([
                        'email' => $request->email,
                        'otp' => $otp
                    ]);
                }
    
                $user->notify(new SendOtp($user, $otp));
                return $this->sendResponse(
                    'OTP Code sent successfully',
                    [
                        'email' => $user->email
                    ]
                );
            } else {
                return $this->sendError(Strings::NetworkError(), [], 501);
            }
        }
    
        public function verify_otp(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'otp' => 'required|integer',
                'email' => 'required|string|email'
            ]);
    
            if($validator->fails()){
                return $this->sendError('Required fields are missing..!', $validator->errors(), 422);
            }
    
            if (User::where('email', $request->email)->exists()) {
                $user = User::where('email', $request->email)->first();
                if (Otp::where('otp', $request->otp)->exists()) {
                    $otp = Otp::where('otp', $request->otp)->first();
                    $otp->delete();
                    $user->update([
                        'email_verified_at' => now()
                    ]);
                    return $this->sendResponse(
                        'OTP Code verified successfully',
                        [
                            'user' => $user
                        ]
                    );
                } else {
                    return $this->sendError('Incorrect OTP Code', [], 501);
                }
            } else {
                return $this->sendError(Strings::RecordNotFound(), [], 500);
            }
        }
    
        public function create_new_password(Request $request)
        {
            $user = User::where('email',$request->email)->first();
            if ($user) {
                $user->password = Hash::make($request->new_password);
                if ($user->update()) {
                    return $this->sendResponse(
                        Strings::PasswordChanged(),
                        [
                            'user' => $user
                        ]
                    );
                } else {
                    return $this->sendError(Strings::NetworkError(), [], 501);
                }
            } else {
                return $this->sendError(Strings::RecordNotFound(), [], 500);
            }
        }

        
    }

