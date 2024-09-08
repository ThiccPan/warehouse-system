<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    /**
     * index handler, fetch all saved Category
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $items = Item::all();
            return response()->json([
                'code' => 200,
                'message' => 'getting all item successfull',
                'data' => $items
            ], 200);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to fetch all item',
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
            $category = Item::findOrFail($id);

            return response()->json([
                'code' => 200,
                'message' => 'getting all item successfull',
                'data' => $category
            ], 200);
        } catch (ModelNotFoundException $e) {
            info($e);
            return response()->json([
                'code' => 404,
                'message' => 'item not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to fetch item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * insert handler, add new category
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function insert(Request $request): JsonResponse
    {
        try {
            $validReq = $request->validate([
                'name' => ['required', 'max:50'],
                'code' => ['required', 'max:4'],
                'category_id' => ['required', 'exists:App\Models\Category,id'],
                'location_id' => ['required', 'exists:App\Models\Location,id'],
                'description' => ['required']
            ]);
            $item = Item::create([
                'name' => $validReq['name'],
                'code' => $validReq['code'],
                'stock' => 0,
                'category_id' => $validReq['category_id'],
                'location_id' => $validReq['location_id'],
                'description' => $validReq['description'],
            ]);
            return response()->json([
                'code' => 200,
                'message' => 'adding new item successfull',
                'data' => $item
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
                'message' => 'failed to add item',
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
                'name' => ['max:50'],
                'code' => ['max:4'],
                'category_id' => ['exists:App\Models\Category,id'],
                'location_id' => ['exists:App\Models\Location,id'],
                'description' => []
            ]);
            $item = Item::findOrFail($id);
            if ($name = $validReq['name'] ?? null) {
                $item->name = $name;
            }
            if ($code = $validReq['code'] ?? null) {
                $item->code = $code;
            }
            if ($category_id = $validReq['category_id'] ?? null) {
                $item->category_id = $category_id;
            }
            if ($location_id = $validReq['location_id'] ?? null) {
                $item->location_id = $location_id;
            }
            if ($description = $validReq['description'] ?? null) {
                $item->description = $description;
            }
            $item->save();
            return response()->json([
                'code' => 200,
                'message' => 'updating item successfull',
                'data' => $item
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'code' => 422,
                'message' => 'input does not match requirement',
                'error' => $e->errors()
            ], 422);
        } catch (ModelNotFoundException $e) {
            info($e);
            return response()->json([
                'code' => 404,
                'message' => 'category not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to update category',
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
            $item = Item::findOrFail($id);
            $item->delete();
            return response()->json([
                'code' => 200,
                'message' => 'deleting item successfull',
                'data' => $item
            ], 200);
        } catch (ModelNotFoundException $e) {
            info($e);
            return response()->json([
                'code' => 404,
                'message' => 'item not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to delete item',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
