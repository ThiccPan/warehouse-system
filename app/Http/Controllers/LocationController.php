<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Validator;

class LocationController extends Controller
{
    /**
     * index handler, fetch all saved location
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $locations = Location::all();
            return response()->json([
                'code' => 200,
                'message' => 'getting all location successfull',
                'data' => $locations
            ], 200);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to get all location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * show handler, fetch based on id sent
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id): JsonResponse
    {
        try {
            $location = Location::findOrFail($id);

            return response()->json([
                'code' => 200,
                'message' => 'getting location successfull',
                'data' => $location
            ], 200);
        } catch (ModelNotFoundException $e) {
            info($e);
            return response()->json([
                'code' => 404,
                'message' => 'location not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to get location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * insert handler, add new location
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert(Request $request): JsonResponse
    {
        try {
            $validReq = $request->validate([
                'name' => ['required', 'max:100'],
                'address' => ['required', 'max:200']
            ]);
            $location = Location::create([
                'name' => $validReq['name'],
                'address' => $validReq['address']
            ]);
            return response()->json([
                'code' => 200,
                'message' => 'adding new location successfull',
                'data' => $location
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'message' => 'input does not match requirement',
                'error' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'failed to add new location',
                'error' => $e
            ], 500);
        }
    }

    /**
     * update handler, update based on id and property sent
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $validReq = $request->validate([
                'name' => ['max:100'],
                'address' => ['max:200']
            ]);
            $location = Location::findOrFail($id);
            info($location);
            info($validReq);
            if ($name = $validReq['name'] ?? null) {
                $location->name = $name;
            }
            if ($address = $validReq['address'] ?? null) {
                $location->address = $address;
            }
            $location->save();
            return response()->json([
                'code' => 200,
                'message' => 'updating location successfull',
                'data' => $location
            ], 200);
        } catch (ModelNotFoundException $e) {
            info($e);
            return response()->json([
                'code' => 404,
                'message' => 'location not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to update location',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * delete handler, deleting based on id sent
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id): JsonResponse
    {
        try {
            $location = Location::findOrFail($id);
            $location->delete();
            return response()->json([
                'code' => 200,
                'message' => 'deleting location successfull',
                'data' => $location
            ], 200);
        } catch (ModelNotFoundException $e) {
            info($e);
            return response()->json([
                'code' => 404,
                'message' => 'location not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to delete location',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
