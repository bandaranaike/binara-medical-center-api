<?php

namespace App\Services;

use App\Enums\AppointmentType;
use App\Models\Doctor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DoctorsDropdownStrategy implements DropdownStrategyInterface
{

    public function getQuery(Request $request): Builder
    {
        $query = Doctor::query();

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%')
                ->where('doctor_type', $request->get('type', AppointmentType::SPECIALIST));
        }

        $query->select(['id', 'name AS label']);

        return $query;
    }
}
