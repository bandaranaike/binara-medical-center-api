<?php

namespace App\Http\Controllers;

use App\Http\Resources\DropdownResource;
use App\Services\DropdownStrategyFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DropdownController extends Controller
{

    const DEFAULT_RESULT_LIMIT = 10;

    public function __construct(private readonly DropdownStrategyFactory $factory)
    {
    }

    public function index(Request $request, $apiUri): JsonResponse
    {
        $strategy = $this->factory->make($apiUri);

        $query = $strategy->getQuery($request);

        $data = $query->limit($request->get('limit', self::DEFAULT_RESULT_LIMIT))->get();

        return new JsonResponse(DropdownResource::collection($data));
    }
}
