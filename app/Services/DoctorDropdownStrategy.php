<?php

namespace App\Services;

use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DoctorDropdownStrategy implements DropdownStrategyInterface
{

    public function getResults(Request $request): Collection
    {
        $query = Doctor::query();

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%');
        }

        $query->select(['id', 'name AS label']);

        return $query->get();
    }
}
