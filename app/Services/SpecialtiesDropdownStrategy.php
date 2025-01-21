<?php

namespace App\Services;

use App\Models\Specialty;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SpecialtiesDropdownStrategy implements DropdownStrategyInterface
{

    public function getQuery(Request $request): Builder
    {
        $query = Specialty::query();

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%');
        }

        $query->select(['id', 'name AS label']);

        return $query;
    }
}
