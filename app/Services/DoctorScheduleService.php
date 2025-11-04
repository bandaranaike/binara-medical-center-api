<?php

namespace App\Services;

use App\Models\DoctorSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DoctorScheduleService
{
    public function generateAvailabilityForDoctorForMonth($doctorId, $override = true, $months = 1): array
    {
        // Calculate month range: first day 00:00 through last day 23:59:59
        $start = Carbon::today();
        $end = $start->copy()->addMonths($months);

        // Pull active weekly schedules for this doctor
        $schedules = DoctorSchedule::query()
            ->where('doctor_id', $doctorId)
            ->where('status', 'active')
            ->get(['id','doctor_id', 'weekday', 'time', 'seats', 'recurring']);

        if ($schedules->isEmpty()) {
            return [
                'ok' => true,
                'message' => 'No active schedules found for this doctor.',
                'inserted' => 0,
                'updated' => 0,
                'skipped' => 0,
            ];
        }

        // If the override is true, need to remove existing records for this doctor
        if ($override) {
            $doctorScheduleIds = $schedules->pluck('id');
            if (!$doctorScheduleIds->isEmpty()) {
                DB::table('doctor_availabilities')->where('doctor_schedule_id', $doctorScheduleIds)->delete();
            }
        }

        // Build the full set of (date, time) slots for the month based on weekday rules
        $rows = [];
        foreach ($schedules as $schedule) {
            $recurring = $schedule->recurring ?? 'Weekly'; // default

            // For "Once", just create one availability on the nearest weekday
            if ($recurring === 'Once') {
                $targetDate = $start->copy()->next($schedule->weekday);
                if ($start->dayOfWeek === (int)$schedule->weekday) {
                    $targetDate = $start->copy();
                }

                if ($targetDate->lte($end)) {
                    $rows[] = [
                        'doctor_id' => $doctorId,
                        'date' => $targetDate->toDateString(),
                        'time' => $schedule->time,
                        'seats' => (int)$schedule->seats,
                        'available_seats' => (int)$schedule->seats,
                        'doctor_schedule_id' => $schedule->id,
                        'status' => 'Active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                continue;
            }

            // Find the first occurrence of this weekday in the month window
            $date = $start->copy()->next($schedule->weekday);
            // If the first "next" jumps beyond the end, check if month starts on same weekday
            if ($start->dayOfWeek === (int)$schedule->weekday && $start->betweenIncluded($start, $end)) {
                $date = $start->copy(); // include the first day itself
            }

            while ($date->lte($end)) {
                $rows[] = [
                    'doctor_id' => $doctorId,
                    'date' => $date->toDateString(),
                    'time' => $schedule->time,
                    'seats' => (int)$schedule->seats,
                    'available_seats' => (int)$schedule->seats,
                    'doctor_schedule_id' => $schedule->id,
                    'status' => 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $date = $this->getNextDate($date, $recurring);
            }
        }

        if (empty($rows)) {
            return [
                'ok' => true,
                'message' => 'No calendar rows generated for the selected month.',
                'inserted' => 0,
                'updated' => 0,
                'skipped' => 0,
            ];
        }

        $inserted = 0;
        $updated = 0;
        $skipped = 0;

        DB::transaction(function () use ($rows, $override, &$inserted, &$updated, &$skipped) {
            if ($override) {
                // Upsert and update seats/available_seats/status on conflict
                foreach (array_chunk($rows, 1000) as $chunk) {
                    DB::table('doctor_availabilities')->upsert(
                        $chunk,
                        ['doctor_id', 'date', 'time'],
                        ['seats', 'available_seats', 'status', 'updated_at']
                    );
                    // upsert() returns number of affected rows (inserted + updated),
                    // but not separated. We'll approximate by counting which already exist.
                    // To give precise counts, pre-check existing keys for each chunk.
                    $keys = collect($chunk)->map(fn($r) => [$r['doctor_id'], $r['date'], $r['time']]);
                    $existing = DB::table('doctor_availabilities')
                        ->whereIn(DB::raw('(doctor_id, date, time)'), $keys->map(fn($k) => DB::raw("($k[0], '$k[1]', '$k[2]')"))->toArray())
                        ->count();

                    $updated += $existing;
                    $inserted += count($chunk) - $existing;
                }
            } else {
                // No override: only insert rows that don't already exist
                // Build a lookup of existing keys to filter out
                $pairs = collect($rows)->map(fn($r) => [
                    'doctor_id' => $r['doctor_id'],
                    'date' => $r['date'],
                    'time' => $r['time'],
                ]);

                // Use a derived table approach to fetch existing keys efficiently
                // (We can also loop-chunk if needed.)
                $existing = DB::table('doctor_availabilities')
                    ->where('doctor_id', $rows[0]['doctor_id'])
                    ->whereBetween('date', [$rows[0]['date'], end($rows)['date']]) // rough bound; still safe
                    ->whereIn(DB::raw('(doctor_id, date, time)'), $pairs->map(
                        fn($p) => DB::raw("({$p['doctor_id']}, '{$p['date']}', '{$p['time']}')")
                    )->toArray())
                    ->get(['doctor_id', 'date', 'time'])
                    ->map(fn($e) => "$e->doctor_id|$e->date|$e->time")
                    ->toArray();

                $existingSet = array_flip($existing);

                $newRows = array_filter($rows, function ($r) use ($existingSet) {
                    $key = "{$r['doctor_id']}|{$r['date']}|{$r['time']}";
                    return !isset($existingSet[$key]);
                });

                $skipped = count($rows) - count($newRows);

                foreach (array_chunk($newRows, 1000) as $chunk) {
                    DB::table('doctor_availabilities')->insert($chunk);
                    $inserted += count($chunk);
                }
            }
        });

        return [
            'ok' => true,
            'message' => $override
                ? 'Availability generated with override.'
                : 'Availability generated without overriding existing records.',
            'inserted' => $inserted,
            'updated' => $updated,
            'skipped' => $skipped,
        ];
    }

    private function getNextDate(Carbon $date, string $recurring): Carbon
    {
        return match ($recurring) {
            'Daily' => $date->addDay(),
            'Bi-Weekly' => $date->addWeeks(2),
            'Monthly' => $date->addMonth(),
            'Bi-Monthly' => $date->addMonths(2),
            'Quarterly' => $date->addMonths(3),
            'Yearly' => $date->addYear(),
            default => $date->addWeek(), // fallback (weekly)
        };
    }
}
