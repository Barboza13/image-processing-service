<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    /**
     * Register new user.
     *
     * @param \App\Http\Requests\LoginRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $user = User::create($validated);

            return response()->json([
                "user" => $user,
                "message" => "¡Usuario registrado exitosamente!"
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error al registrar un usuario: ' . $e->getMessage());
            return response()->json(["error" => "Ocurrio un error inesperado al registrar al usuario", 500]);
        }
    }
}
