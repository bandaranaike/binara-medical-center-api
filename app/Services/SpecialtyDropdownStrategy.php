<?php

namespace App\Services;

use App\Models\Hospital;
use App\Models\Specialty;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class SpecialtyDropdownStrategy implements DropdownStrategyInterface
{

    public function getResults(Request $request): Collection
    {
        $query = Specialty::query();

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%');
        }

        $query->select(['id', 'name AS label']);

        return $query->get();
    }
}
