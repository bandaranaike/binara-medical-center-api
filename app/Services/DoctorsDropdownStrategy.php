<?php

namespace App\Services;

use App\Enums\AppointmentType;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorsDropdownStrategy implements DropdownStrategyInterface
{
    public function getQuery(Request $request): Builder
    {
        $query = Doctor::query();
        $query->join('doctor_availabilities', 'doctors.id', '=', 'doctor_availabilities.doctor_id');

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%');
        }

        if ($request->has('type')) {
            $query->where('doctor_type', $request->get('type', AppointmentType::SPECIALIST));
        }

        // If booking is true and the date is not null, we will filter the doctors that are available on that date
        if ($request->has('is-booking') && $request->get('is-booking') == 'true' && $request->get('date')) {
            $query->where('date', $request->get('date'));
        } // If booking is false show only the doctors that are available on the current date
        elseif ($request->has('is-booking') && $request->get('is-booking') == 'false') {
            $query->where('date', Carbon::today());
        }

        $query->select([
            'doctors.id',
            DB::raw('MAX(name) AS label'), // Use MAX for the name as well
            'doctor_id',
            DB::raw('MAX(CONCAT(date, " - ", time)) AS extra') // Use MAX for date and time
        ]);

        $query->groupBy('doctors.id', 'doctor_id');


        return $query;
    }
}
