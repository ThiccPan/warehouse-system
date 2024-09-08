<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * register handler
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            info($request);
            $validReq = $request->validate([
                'name' => ['required', 'max:100'],
                'email' => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'min:8', 'alpha_num']
            ]);
            $user = User::create([
                'name' => $validReq['name'],
                'email' => $validReq['email'],
                'password' => $validReq['password']
            ]);
            return response()->json([
                'code' => 200,
                'message' => 'new user successfully registered',
                'data' => $user
            ], 200);
        }
        // catch input validation exception
        catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'message' => 'input does not match requirement',
                'error' => $e->errors()
            ], 422);
        }
        // catch other exception
        catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'failed to register new user',
                'error' => $e
            ], 500);
        }
    }

    /**
     * login handler
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            info($request);
            $validReq = $request->validate([
                'email' => ['required', 'email'],
                'password' => ['required', 'min:8', 'alpha_num']
            ]);
            $auth = Auth::attempt([
                'email' => $validReq['email'],
                'password' => $validReq['password']
            ]);
            if (!$auth) {
                return response()->json([
                    'code' => 401,
                    'error' => 'invalid credentials',
                ], 401);
            }
            $user = Auth::user();
            // deleting old token before assigning a new one
            // in case of still active token
            $user->tokens()->delete();
            return response()->json([
                'code' => 200,
                'message' => 'login attempt successfull',
                'data' => [
                    'token' => $user->createToken('API_token')->plainTextToken
                ]
            ], 200);
        }
        // catch input validation exception
        catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'message' => 'input does not match requirement',
                'error' => $e->errors()
            ], 422);
        }
        // catch other exception
        catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'failed to login',
                'error' => $e
            ], 500);
        }
    }

    /**
     * logout handler
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            info($request);
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'code' => 400,
                    'message' => 'already logged out',
                    'error' => ''
                ], 400);
            }
            $user->tokens()->delete();
            return response()->json([
                'code' => 200,
                'message' => 'logout attempt successfull',
                'data' => []
            ], 200);
        }
        // catch other exception
        catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'failed to logout',
                'error' => $e
            ], 500);
        }
    }
}
