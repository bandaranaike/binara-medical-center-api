<?php

namespace App\Services;

use App\Enums\AppointmentType;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AllDoctorsDropdownStrategy implements DropdownStrategyInterface
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

        $query->select([
            'doctors.id',
            'name AS label', // Use MAX for the name as well
        ]);


        return $query;
    }
}
