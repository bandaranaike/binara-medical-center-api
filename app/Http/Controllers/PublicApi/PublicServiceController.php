<?php

namespace App\Http\Controllers\PublicApi;

use App\Http\Controllers\Controller;
use App\Http\Requests\PublicApi\SearchPublicServiceRequest;
use App\Http\Requests\PublicApi\StorePublicServiceRequest;
use App\Models\Service;
use App\Services\PublicBillingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class PublicServiceController extends Controller
{
    public function __construct(private readonly PublicBillingService $publicBillingService) {}

    public function index(SearchPublicServiceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $type = ($validated['type'] ?? null) === 'treatment'
            ? 'others'
            : ($validated['type'] ?? null);
        $query = trim($validated['query']);

        $services = $this->matchingServices($query)
            ->take(50)
            ->filter(function (Service $service) use ($type): bool {
                if ($type === null) {
                    return true;
                }

                return $this->publicBillingService->publicServiceTypeForService($service) === $type;
            })
            ->values()
            ->map(fn (Service $service): array => $this->serialize($service));

        return response()->json([
            'data' => $services,
        ]);
    }

    public function search(SearchPublicServiceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $type = ($validated['type'] ?? null) === 'treatment'
            ? 'others'
            : ($validated['type'] ?? null);
        $query = trim($validated['query']);

        $services = $this->matchingServices($query)
            ->filter(function (Service $service) use ($type): bool {
                return $type === null
                    || $this->publicBillingService->publicServiceTypeForService($service) === $type;
            })
            ->take(8)
            ->map(fn (Service $service): array => $this->serialize($service))
            ->values();

        return response()->json(['data' => $services]);
    }

    public function store(StorePublicServiceRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $service = Service::query()->firstOrCreate(
            ['key' => $validated['key']],
            [
                'name' => $validated['name'],
                'bill_price' => $validated['bill_price'],
                'system_price' => $validated['system_price'],
            ],
        );

        return response()->json($this->serialize($service), $service->wasRecentlyCreated ? 201 : 200);
    }

    /**
     * @return array{id: int, name: string, key: string, type: string, system_price: float, bill_price: float}
     */
    private function serialize(Service $service): array
    {
        return [
            'id' => $service->id,
            'name' => $service->name,
            'key' => $service->key,
            'type' => $this->publicBillingService->publicServiceTypeForService($service),
            'system_price' => (float) $service->system_price,
            'bill_price' => (float) $service->bill_price,
        ];
    }

    /**
     * @return \Illuminate\Support\Collection<int, Service>
     */
    private function matchingServices(string $query): Collection
    {
        return Service::query()
            ->where(function ($builder) use ($query): void {
                $builder
                    ->whereRaw('lower(name) like lower(?)', ['%'.$query.'%'])
                    ->orWhereRaw('lower(`key`) like lower(?)', ['%'.$query.'%']);
            })
            ->orderByRaw(
                'case
                    when lower(name) = lower(?) or lower(`key`) = lower(?) then 0
                    when lower(name) like lower(?) or lower(`key`) like lower(?) then 1
                    else 2
                end',
                [$query, $query, $query.'%', $query.'%']
            )
            ->orderBy('name')
            ->get();
    }
}
