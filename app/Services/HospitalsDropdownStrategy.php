<?php

namespace App\Services;

use App\Models\Hospital;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class HospitalsDropdownStrategy implements DropdownStrategyInterface
{

    public function getQuery(Request $request): Builder
    {
        $query = Hospital::query();

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%')
                ->orWhere('location', 'LIKE', '%' . $request->get('search') . '%');
        }

        $query->select(['id', 'name AS label']);

        return $query;
    }
}
