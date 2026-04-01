<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\DaySummaryReportRequest;
use App\Services\DaySummaryReportService;
use Illuminate\Http\JsonResponse;

class PublicReportController extends Controller
{
    public function __construct(private readonly DaySummaryReportService $daySummaryReportService) {}

    public function daySummary(DaySummaryReportRequest $request): JsonResponse
    {
        $validated = $request->validated();

        return response()->json($this->daySummaryReportService->build($validated['date'], $validated['shift']));
    }
}
