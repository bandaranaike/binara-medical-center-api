<?php

namespace App\Services;

use App\Enums\AppointmentType;
use App\Enums\ServiceKey;
use App\Models\Bill;
use App\Models\BillItem;
use App\Models\Service;
use Illuminate\Support\Str;

class PublicBillingService
{
    public function normalizeServiceType(?string $serviceType): ?string
    {
        if ($serviceType === null) {
            return null;
        }

        return match (Str::lower(trim($serviceType))) {
            'others' => AppointmentType::TREATMENT->value,
            default => Str::lower(trim($serviceType)),
        };
    }

    public function publicServiceTypeForService(Service $service): string
    {
        return match (true) {
            $service->key === ServiceKey::DEFAULT_DOCTOR->value => AppointmentType::OPD->value,
            $service->key === ServiceKey::DEFAULT_SPECIALIST_CHANNELING->value => AppointmentType::SPECIALIST->value,
            str_starts_with($service->key, 'dental-') => AppointmentType::DENTAL->value,
            str_starts_with($service->key, 'opd-') => AppointmentType::OPD->value,
            str_starts_with($service->key, 'specialist-') => AppointmentType::SPECIALIST->value,
            str_starts_with($service->key, 'treatment-') => AppointmentType::TREATMENT->value,
            default => 'others',
        };
    }

    public function replaceBillItems(Bill $bill, array $items, ?int $fallbackDoctorId, ?string $fallbackCategory): void
    {
        $bill->billItems()->delete();

        foreach ($items as $item) {
            $service = $this->resolveService($item);
            $referredAmount = (float) $item['referred_amount'];
            $systemAmount = (float) $item['system_amount'];

            BillItem::query()->create([
                'bill_id' => $bill->id,
                'service_id' => $service->id,
                'service_name' => $item['service_name'] ?? $service->name,
                'service_key' => $service->key,
                'doctor_id' => $item['doctor_id'] ?? $fallbackDoctorId,
                'system_amount' => $systemAmount,
                'referred_amount' => $referredAmount,
                'category' => $this->normalizeCategory($item['category'] ?? $fallbackCategory),
                'is_ad_hoc' => (bool) ($item['is_ad_hoc'] ?? false),
            ]);
        }

        $bill->syncAmounts();
    }

    public function createDefaultBillItem(
        Bill $bill,
        Service $service,
        float $referredAmount,
        float $systemAmount,
        ?int $doctorId,
        ?string $category,
    ): void {
        BillItem::query()->create([
            'bill_id' => $bill->id,
            'service_id' => $service->id,
            'service_name' => $service->name,
            'service_key' => $service->key,
            'doctor_id' => $doctorId,
            'system_amount' => $systemAmount,
            'referred_amount' => $referredAmount,
            'category' => $this->normalizeCategory($category),
            'is_ad_hoc' => false,
        ]);

        $bill->syncAmounts();
    }

    /**
     * @return array<string, mixed>
     */
    public function serializeBillItem(BillItem $billItem): array
    {
        $serviceName = $billItem->service_name ?? $billItem->service?->name;
        $serviceKey = $billItem->service_key ?? $billItem->service?->key;
        $systemAmount = (float) $billItem->system_amount;
        $referredAmount = (float) $billItem->referred_amount;
        $totalAmount = round($referredAmount + $systemAmount, 2);

        return [
            'service_id' => $billItem->service_id,
            'service_key' => $serviceKey,
            'service_name' => $serviceName,
            'system_amount' => $systemAmount,
            'referred_amount' => $referredAmount,
            'total_amount' => $totalAmount,
            'doctor_id' => $billItem->doctor_id,
            'category' => $billItem->category,
            'is_ad_hoc' => (bool) $billItem->is_ad_hoc,
            'name' => $serviceName,
            'price' => number_format($totalAmount, 2, '.', ''),
        ];
    }

    private function resolveService(array $item): Service
    {
        $serviceId = $item['service_id'] ?? null;

        if (is_numeric($serviceId) && (int) $serviceId > 0) {
            return Service::query()->findOrFail((int) $serviceId);
        }

        $serviceKey = $item['service_key'] ?? null;

        if (is_string($serviceKey) && $serviceKey !== '') {
            $service = Service::query()->where('key', $serviceKey)->first();

            if ($service !== null) {
                return $service;
            }
        }

        $serviceName = trim((string) ($item['service_name'] ?? ''));

        return Service::query()->create([
            'name' => $serviceName,
            'key' => $this->generateUniqueKey($serviceName),
            'bill_price' => (float) $item['referred_amount'] + (float) $item['system_amount'],
            'system_price' => (float) $item['system_amount'],
        ]);
    }

    private function generateUniqueKey(string $serviceName): string
    {
        $baseKey = Str::slug($serviceName);
        $baseKey = $baseKey !== '' ? $baseKey : 'service';
        $candidate = $baseKey;
        $suffix = 2;

        while (Service::query()->where('key', $candidate)->exists()) {
            $candidate = $baseKey.'-'.$suffix;
            $suffix++;
        }

        return $candidate;
    }

    private function normalizeCategory(?string $category): ?string
    {
        if ($category === null || trim($category) === '') {
            return null;
        }

        return match (Str::lower(trim($category))) {
            AppointmentType::TREATMENT->value => 'others',
            default => Str::lower(trim($category)),
        };
    }
}
