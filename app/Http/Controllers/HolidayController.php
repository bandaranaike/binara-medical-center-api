<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreHolidayRequest;
use App\Http\Requests\UpdateHolidayRequest;
use App\Models\Holiday;
use Illuminate\Http\JsonResponse;

class HolidayController extends Controller
{
    use CrudTrait;

    public function __construct()
    {
        $this->model = new Holiday;
        $this->updateRequest = new UpdateHolidayRequest;
        $this->storeRequest = new StoreHolidayRequest;
    }

    public function todayStatus(): JsonResponse
    {
        $today = now()->toDateString();

        $holiday = Holiday::query()
            ->whereDate('date', $today)
            ->where('is_closed', true)
            ->first();

        return response()->json([
            'date' => $today,
            'is_closed' => (bool) $holiday,
            'holiday_name' => $holiday?->name,
            'message' => $holiday?->message,
        ]);
    }

    public function store(StoreHolidayRequest $request): JsonResponse
    {
        $holiday = Holiday::query()->create($request->validated());

        return new JsonResponse([
            'message' => 'Record created successfully',
            'item' => $holiday,
        ], 201);
    }

    public function update(UpdateHolidayRequest $request, $id): JsonResponse
    {
        $this->model::findOrFail($id)->update($request->validated());

        return new JsonResponse([
            'message' => 'Record updated successfully',
        ]);
    }
}
