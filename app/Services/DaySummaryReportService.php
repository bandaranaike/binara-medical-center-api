<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DaySummaryReportService
{
    /**
     * @return array{start_date: string, end_date: string, items: array<int, array{service_name: string, quantity: int, total: float}>}
     */
    public function build(string $date, string $shift): array
    {
        $reportDate = Carbon::parse($date)->toDateString();

        $items = DB::table('bill_items')
            ->join('bills', 'bill_items.bill_id', '=', 'bills.id')
            ->join('services', 'bill_items.service_id', '=', 'services.id')
            ->leftJoin('doctors', 'bills.doctor_id', '=', 'doctors.id')
            ->selectRaw(
                "CASE WHEN services.`key` = ? THEN CONCAT(services.name, ' ', MAX(doctors.name)) ELSE services.name END as service_name",
                ['channeling'],
            )
            ->selectRaw('COUNT(bill_items.id) as quantity')
            ->selectRaw('SUM(bill_items.bill_amount) as total')
            ->whereDate('bills.date', $reportDate)
            ->where('bills.shift', $shift)
            ->where('bills.payment_status', 'paid')
            ->whereNull('bills.deleted_at')
            ->groupByRaw(
                "services.`key`, services.name, CASE WHEN services.`key` = 'channeling' THEN bills.doctor_id ELSE 0 END",
            )
            ->havingRaw('SUM(bill_items.bill_amount) > 0')
            ->orderByDesc('total')
            ->get()
            ->map(static fn ($item): array => [
                'service_name' => $item->service_name,
                'quantity' => (int) $item->quantity,
                'total' => (float) $item->total,
            ])
            ->values()
            ->all();

        return [
            'start_date' => $reportDate,
            'end_date' => $reportDate,
            'items' => $items,
        ];
    }
}
