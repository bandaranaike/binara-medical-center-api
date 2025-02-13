<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceAdminController extends Controller
{
    public function index(): JsonResponse
    {
        return new JsonResponse(Service::all());
    }
}
