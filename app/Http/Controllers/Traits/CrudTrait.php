<?php

namespace App\Http\Controllers\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
    protected array $relationships = [];

    protected string $resource;

    public function index(Request $request)
    {
        $query = $this->model->query();

        if ($request->get('searchField') && $request->get('searchValue')) {

            $searchValue = $request->get('searchValue');
            $searchField = $request->get('searchField');

            if (count($this->relationships) > 0 && Str::contains($searchField, ':')) {
                [$relationShip, $field] = explode(':', $request->get('searchField'));
                $query->whereHas($relationShip, function ($query) use ($searchValue, $field) {
                    $query->where($field, 'LIKE', '%' . $searchValue . '%');
                });
            } else {
                $query = $query->where($request->get('searchField'), 'LIKE', "%" . $request->get('searchValue') . "%");
            }
        }

        $records = $query->with($this->relationships)->paginate(self::DEFAULT_PAGE_SIZE);

        if (isset($this->resource)) {
            $data = $this->resource::collection($records);
        } else {
            $data = $records->items();
        }
        return new JsonResponse(["data" => $data, "last_page" => $records->lastPage()]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validate($request, $this->storeRequest->rules());
        $item = $this->model::create($validated);
        return new JsonResponse(['message' => 'Record created successfully', "item" => $item], 201);
    }

    public function show($id)
    {
        return $this->model::findOrFail($id);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validated = $this->validate($request, $this->updateRequest->rules());
        $this->model::findOrFail($id)->update($validated);
        return new JsonResponse(['message' => 'Record updated successfully']);
    }

    public function destroy($id): JsonResponse
    {
        $ids = explode(',', $id);
        $this->model::whereIn('id', $ids)->delete();
        return new JsonResponse(['message' => 'Record deleted successfully']);
    }
}
