<?php

namespace App\Services;

use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class HospitalDropdownStrategy implements DropdownStrategyInterface
{

    public function getResults(Request $request): Collection
    {
        $query = Hospital::query();

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%')
                ->orWhere('location', 'LIKE', '%' . $request->get('search') . '%');
        }

        $query->select(['id', 'name AS label']);

        return $query->get();
    }
}
