<?php

namespace App\Services;

use App\Enums\AppointmentType;
use App\Models\Doctor;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OPDDoctorsDropdownStrategy implements DropdownStrategyInterface
{

    public function getQuery(Request $request): Builder
    {
        $query = Doctor::query();

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%')
                ->where('doctor_type', AppointmentType::OPD);
        }

        $query->select(['id', 'name AS label']);

        return $query;
    }
}
