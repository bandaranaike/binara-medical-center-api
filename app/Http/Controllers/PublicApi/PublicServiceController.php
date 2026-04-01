<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicApi\SearchPublicServiceRequest;
use App\Models\Service;
use App\Services\PublicBillingService;
use Illuminate\Http\JsonResponse;

class PublicServiceController extends Controller
{
    public function __construct(private readonly PublicBillingService $publicBillingService) {}

    public function search(SearchPublicServiceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $type = $validated['type'] ?? null;
        $query = trim($validated['query']);

        $services = Service::query()
            ->where('name', 'like', '%'.$query.'%')
            ->orderByRaw(
                'case
                    when name = ? then 0
                    when name like ? then 1
                    else 2
                end',
                [$query, $query.'%']
            )
            ->orderBy('name')
            ->limit(50)
            ->get()
            ->filter(function (Service $service) use ($type): bool {
                if ($type === null) {
                    return true;
                }

                return $this->publicBillingService->publicServiceTypeForService($service) === $type;
            })
            ->values()
            ->map(function (Service $service): array {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'key' => $service->key,
                    'type' => $this->publicBillingService->publicServiceTypeForService($service),
                    'system_price' => (float) $service->system_price,
                    'bill_price' => (float) $service->bill_price,
                ];
            });

        return response()->json([
            'data' => $services,
        ]);
    }
}
