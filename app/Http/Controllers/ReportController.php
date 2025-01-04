<?php

namespace App\Http\Controllers;

use App\Models\Bill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private string $start;
    private string $end;

    private function getTotalRevenue(): array
    {

        $billData = Bill::selectRaw('SUM(bill_amount) as totalRevenue, SUM(system_amount) as totalSystemRevenue')
            ->whereBetween('created_at', [$this->start, $this->end])
            ->first();

        return $billData->toArray();
    }

    private function getRevenueByDoctor(): array
    {

        $revenueByDoctor = DB::table('bills')
            ->join('doctors', 'bills.doctor_id', '=', 'doctors.id')
            ->select('doctors.name as doctorName', DB::raw('SUM(bills.bill_amount) as revenue'))
            ->whereBetween('bills.created_at', [$this->start, $this->end])
            ->whereNull('bills.deleted_at') // Exclude soft-deleted bills
            ->groupBy('doctors.id', 'doctors.name')
            ->orderBy('revenue', 'desc')
            ->get();

        return $revenueByDoctor->toArray();
    }

    private function getBillStatusSummary(): array
    {

        $statuses = DB::table('bills')
            ->select('status', DB::raw('COUNT(*) as count'))
            ->whereBetween('created_at', [$this->start, $this->end])
            ->groupBy('status')
            ->get();

        $count = DB::table('bills')
            ->whereBetween('created_at', [$this->start, $this->end])
            ->count();

        return ["statusData" => $statuses, "count" => $count];
    }

    private function getDailyReportSummary(): array
    {

        // Newly registered and updated patients count
        $patientsCounts = DB::table('patients')
            ->selectRaw("
                COALESCE(COUNT(*), 0) as newPatientsCount,
                COALESCE(SUM(CASE WHEN created_at < updated_at THEN 1 ELSE 0 END), 0) as updatedPatientsCount")
            ->whereBetween('created_at', [$this->start, $this->end])
            ->first();

        // Visited doctors count (unique)
        $visitedDoctorsCount = DB::table('bills')
            ->whereBetween('created_at', [$this->start, $this->end])
            ->whereNotNull('doctor_id') // Ensure doctor is assigned
            ->where('status', Bill::STATUS_DONE)
            ->distinct('doctor_id')
            ->count('doctor_id');

        return [
            'newPatients' => $patientsCounts->newPatientsCount,
            'updatedPatients' => $patientsCounts->updatedPatientsCount,
            'visitedDoctors' => $visitedDoctorsCount,
        ];
    }

    private function setStartEndDates(Request $request): void
    {
        $this->start = $request->get('start', now()->startOfDay()->toDateTimeString());
        $this->end = $request->get('end', now()->endOfDay()->toDateTimeString());
    }

    public function index(Request $request): JsonResponse
    {
        $this->setStartEndDates($request);

        return response()->json([
            'totalRevenue' => $this->getTotalRevenue(),
            'revenueByDoctor' => $this->getRevenueByDoctor(),
            'billStatusSummary' => $this->getBillStatusSummary(),
            'dailyReportSummary' => $this->getDailyReportSummary(),
        ]);
    }
}
