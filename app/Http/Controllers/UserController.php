<?php

namespace App\Http\Controllers;

use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
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

    public function index(): JsonResponse
    {
        try {
            $users = User::all();
            return response()->json([
                'code' => 200,
                'message' => 'getting all user successfull',
                'data' => $users
            ], 200);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to fetch all user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = Auth::user();
            $validReq = $request->validate([
                'name' => ['max:100'],
                'email' => ['email', 'unique:users,email'],
                'password' => ['min:8', 'alpha_num']
            ]);
            if ($newName = $validReq['name'] ?? null) {
                $user->name = $newName;
            }
            if ($newEmail = $validReq['email'] ?? null) {
                $user->email = $newEmail;
            }
            if ($newPass = $validReq['password'] ?? null) {
                $user->password = $newPass;
            }
            $user->save();
            return response()->json([
                'code' => 200,
                'message' => 'update user successfull',
                'data' => $user
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'message' => 'input does not match requirement',
                'error' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to update user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function deleteProfile()
    {
        try {
            $user = Auth::user();
            // delete token
            $user->tokens()->delete();
            // delete profile
            $user->delete();
            return response()->json([
                'code' => 200,
                'message' => 'delete user successfull',
                'data' => $user
            ], 200);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to delete user',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function getUserMutation($id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            return response()->json([
                'code' => 200,
                'message' => 'fetching user mutations history success',
                'data' => [
                    'mutations' => $user->mutations
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'message' => 'user not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'failed fetching user mutations history',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
