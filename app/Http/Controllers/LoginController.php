<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /**
     * Register new user.
     *
     * @param \App\Http\Requests\LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $user = User::create($validated);

            return response()->json([
                "user" => $user,
                "message" => "User successfully registered!"
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error registering a user: ' . $e->getMessage());
            return response()->json(["error" => "An unexpected error occurred while registering the user!", 500]);
        }
    }

    /**
     * Login user.
     *
     * @param \App\Http\Requests\LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $user = User::where("email", $validated["email"])->first();

            if (!$user || !Hash::check($validated["password"], $user->password)) {
                return response()->json(["message" => "Invalid credentials!"], 401);
            }

            $user->tokens()->delete(); // Delete if exists others tokens.
            $token = $user->createToken("login-token")->plainTextToken;
            return response()->json(["user" => $user, "token" => $token], 200);
        } catch (\Exception $e) {
            Log::error("Login error: " . $e->getMessage());
            return response()->json(["error" => "An unexpected error occurred while logging in!"], 500);
        }
    }

    /**
     * Logout user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request): JsonResponse
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(["message" => "Session ended!", 200]);
        } catch (\Exception $e) {
            Log::error("Error logging out: " . $e->getMessage());
            return response()->json(["error" => "An unexpected error occurred while logging out!"], 500);
        }
    }
}
