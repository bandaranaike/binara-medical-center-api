<?php

namespace App\Services;

use App\Enums\AppointmentType;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DoctorsDropdownStrategy implements DropdownStrategyInterface
{
    public function getQuery(Request $request): Builder
    {
        $query = Doctor::query();

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%');
        }

        if ($request->has('type')) {
            $query->where('doctor_type', $request->get('type', AppointmentType::SPECIALIST));
        }

        if ($request->has('is-booking') && $request->get('is-booking') == "false") {
            $query->whereHas('doctorAvailabilities', function (Builder $query) {
                $query->where('date', Carbon::now()->format('Y-m-d'));
            });
        }

        $query->select(['id', 'name AS label']);

        return $query;
    }
}
