<?php

namespace App\Services;

use App\Models\DoctorSchedule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DoctorSchedulesDropdownStrategy implements DropdownStrategyInterface
{
    public function getQuery(Request $request): Builder
    {
        $query = DoctorSchedule::query();

        // If a search query is present, filter by the name of the allergy
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('weekday', 'LIKE', '%' . $search . '%');
            $query->where('time', 'LIKE', '%' . $search . '%');
            $query->where('recurring', 'LIKE', '%' . $search . '%');
        }

        $query->select([DB::raw("CONCAT(recurring, '-', weekday, '-',time) as label"), 'id',]);

        return $query;
    }
}
