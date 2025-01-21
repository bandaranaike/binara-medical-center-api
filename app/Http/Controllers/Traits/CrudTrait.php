<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @method validate(Request $request, $rules)
 */
trait CrudTrait
{

    use ValidatesRequests;

    const DEFAULT_PAGE_SIZE = 30;

    protected Model $model;
    protected Request $storeRequest;
    protected Request $updateRequest;
    protected string $searchField;

    public function index(Request $request)
    {
        if ($request->has('search')) {
            return $this->model::where($this->searchField, 'LIKE', "%" . $request->get('search') . "%")->paginate(self::DEFAULT_PAGE_SIZE);
        }

        return $this->model::paginate(self::DEFAULT_PAGE_SIZE);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validate($request, $this->storeRequest->rules());
        $this->model::create($validated);
        return response()->json(['message' => 'Record created successfully'], 201);
    }

    public function show($id)
    {
        return $this->model::findOrFail($id);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $this->validate($request, $this->updateRequest->rules());
        $this->model::findOrFail($id)->update($validated);
        return response()->json(['message' => 'Record updated successfully']);
    }

    public function destroy($id): JsonResponse
    {
        $this->model::findOrFail($id)->delete();
        return response()->json(['message' => 'Record deleted successfully']);
    }
}
