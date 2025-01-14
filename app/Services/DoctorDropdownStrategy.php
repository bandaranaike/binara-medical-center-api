<?php

namespace App\Services;

use App\Models\Doctor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class DoctorDropdownStrategy implements DropdownStrategyInterface
{

    public function getQuery(Request $request): Builder
    {
        $query = Doctor::query();

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%')
                ->where('is_opd', false);
        }

        $query->select(['id', 'name AS label']);

        return $query;
    }
}
