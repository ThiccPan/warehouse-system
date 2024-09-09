<?php

namespace App\Http\Controllers;

use App\Enums\MutationTypes;
use App\Models\Item;
use App\Models\Mutation;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MutationController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $mutations = Mutation::all();
            return response()->json([
                'code' => 200,
                'message' => 'getting all mutation successfull',
                'data' => $mutations
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'code' => 500,
                'message' => 'failed to fetch all mutation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $mutations = Mutation::findOrFail($id);
            return response()->json([
                'code' => 200,
                'message' => 'fetching mutation successfull',
                'data' => $mutations
            ], 200);
        } catch (ModelNotFoundException $e) {
            info($e);
            return response()->json([
                'code' => 404,
                'message' => 'mutation not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to fetch mutation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function insert(Request $request): JsonResponse
    {
        try {
            $validReq = $request->validate([
                'item_id' => ['required', 'exists:App\Models\Item,id'],
                'user_id' => ['required', 'exists:App\Models\User,id'],
                'type' => ['required', 'alpha_num', Rule::in(array_column(MutationTypes::cases(), 'value'))],
                'amount' => ['required', 'numeric'],
                'date' => ['required', 'date'],
                'description' => ['required']
            ]);
            $this->validateReqAmount($validReq);

            // if subtract then check if item stock is sufficient for mutation request
            $item = Item::findOrFail($validReq['item_id']);
            $this->validateStockBeforeUpdate($validReq, $item);
            // done validating request

            $mutation = Mutation::create([
                'item_id' => $validReq['item_id'],
                'user_id' => $validReq['user_id'],
                'type' => $validReq['type'],
                'amount' => $validReq['amount'],
                'date' => $validReq['date'],
                'description' => $validReq['description'],
            ]);

            // updating item stock value based on mutation amount request
            $addedStock = $mutation->amount;
            $item->stock += $addedStock;
            $item->save();
            info('updating stock success');
            return response()->json([
                'code' => 200,
                'message' => 'adding new mutation successfull',
                'data' => [
                    'item' => $item,
                    'mutation' => $mutation
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            info($e);
            return response()->json([
                'code' => 404,
                'message' => 'mutation not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (ValidationException $e) {
            info($e);
            return response()->json([
                'code' => 422,
                'message' => 'invalid input',
                'error' => $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to add new mutation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validReq = $request->validate([
                'type' => ['alpha_num', Rule::in(array_column(MutationTypes::cases(), 'value'))],
                'amount' => ['numeric'],
                'date' => ['date'],
                'description' => []
            ]);
            $mutation = Mutation::findOrFail($id);
            $item = $mutation->item;
            $newType = $validReq['type'] ?? null;
            $newAmount = $validReq['amount'] ?? null;

            // item stock validation
            // change in amount and/or type must be included in json for both field
            if ($newType xor $newAmount) {
                throw ValidationException::withMessages(['error' => 'amount and type must be included together']);
            }
            if ($newType && $newAmount) {
                $this->validateReqAmount($validReq);

                // revert item stock from old mutation data change
                info('change in amount');
                $revertStockAmount = $item->stock - $mutation->amount;
                if ($revertStockAmount < 0) {
                    throw ValidationException::withMessages(['error' => 'insufficent item stock for reverting mutation']);
                }
                $item->stock = $revertStockAmount;
                $this->validateStockBeforeUpdate($validReq, $item);

                // update mutation type and amount
                $mutation->type = $newType;
                $mutation->amount = $newAmount;

                // then update item stock to reflect change in mutation amount
                $item->stock += $newAmount;
                $item->save();
            }

            // update mutation based on request sent
            if ($date = $validReq['date'] ?? null) {
                $mutation->date = $date;
            };
            if ($description = $validReq['description'] ?? null) {
                $mutation->description = $description;
            };

            $mutation->save();
            return response()->json([
                'code' => 200,
                'message' => 'updating new mutation successfull',
                'data' => [
                    'item' => $item,
                    'mutation' => $mutation
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            info($e);
            return response()->json([
                'code' => 404,
                'message' => 'mutation not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (ValidationException $e) {
            info($e);
            return response()->json([
                'code' => 422,
                'message' => 'invalid input',
                'error' => $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to update mutation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function delete($id): JsonResponse
    {
        try {
            $mutation = Mutation::findOrFail($id);
            $item = $mutation->item;

            // check if item stock is sufficient before reverting mutation
            if ($item->stock - $mutation->amount < 0) {
                throw ValidationException::withMessages(['error' => 'insufficent item stock for deleting mutation']);
            }
            // revert mutation item stock by subtracting the stock with old mutation amount
            $item->stock -= $mutation->amount;
            $item->save();

            // delete mutation after validation process
            $mutation->delete();

            return response()->json([
                'code' => 200,
                'message' => 'deleting mutation successfull',
                'data' => [
                    'item' => $item,
                    'mutation' => $mutation
                ]
            ], 200);
        } catch (ModelNotFoundException $e) {
            info($e);
            return response()->json([
                'code' => 404,
                'message' => 'mutation not found',
                'error' => $e->getMessage()
            ], 404);
        } catch (ValidationException $e) {
            info($e);
            return response()->json([
                'code' => 422,
                'message' => 'invalid input',
                'error' => $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            info($e);
            return response()->json([
                'code' => 500,
                'message' => 'failed to delete mutation',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function validateReqAmount($validReq)
    {
        // check if type add or subtract
        // if add then amount > 0
        if ($validReq['type'] == MutationTypes::addition->value && $validReq['amount'] < 0) {
            throw ValidationException::withMessages(['error' => 'insert positive number for addition type']);
        }
        // if subtract then amount < 0
        if (
            $validReq['type'] == MutationTypes::subtraction->value
            && $validReq['amount'] > 0
        ) {
            throw ValidationException::withMessages(['error' => 'insert negative number for subtraction type']);
        }
    }

    private function validateStockBeforeUpdate($validReq, $item)
    {
        if (
            $validReq['type'] == MutationTypes::subtraction->value
            && $item->stock + $validReq['amount'] < 0
        ) {
            throw ValidationException::withMessages(['error' => 'insufficent item stock for mutation']);
        }
    }
}
