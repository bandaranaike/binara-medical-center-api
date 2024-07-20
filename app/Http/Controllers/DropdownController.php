<?php

namespace App\Http\Controllers;

use App\Http\Resources\DropdownResource;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\DropdownStrategyFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DropdownController extends Controller
{
    public function __construct(private readonly DropdownStrategyFactory $factory)
    {
    }

    public function index(Request $request, $apiUri): JsonResponse
    {
        $strategy = $this->factory->make($apiUri);

        $data = $strategy->getResults($request);

        return new JsonResponse(DropdownResource::collection($data));
    }
}
