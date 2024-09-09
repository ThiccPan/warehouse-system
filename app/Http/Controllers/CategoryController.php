<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
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
            $categories = Category::all();
            return response()->json([
                'code' => 200,
                'message' => 'getting all category successfull',
                'data' => $categories
            ], 200);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to get all category',
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
            $category = Category::findOrFail($id);

            return response()->json([
                'code' => 200,
                'message' => 'getting category successfull',
                'data' => $category
            ], 200);
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
                'message' => 'failed to get category',
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
            ]);
            $category = Category::create([
                'name' => $validReq['name'],
            ]);
            return response()->json([
                'code' => 200,
                'message' => 'adding new category successfull',
                'data' => $category
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
                'message' => 'failed to add category',
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
            $category = Category::findOrFail($id);
            info($category);
            info($validReq);
            if ($name = $validReq['name'] ?? null) {
                $category->name = $name;
            }
            if ($address = $validReq['address'] ?? null) {
                $category->address = $address;
            }
            $category->save();
            return response()->json([
                'code' => 200,
                'message' => 'updating category successfull',
                'data' => $category
            ], 200);
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
            $category = Category::findOrFail($id);
            $category->delete();
            return response()->json([
                'code' => 200,
                'message' => 'deleting category successfull',
                'data' => $category
            ], 200);
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
                'message' => 'failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
