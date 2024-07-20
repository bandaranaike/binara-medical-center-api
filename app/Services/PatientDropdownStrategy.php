<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PatientDropdownStrategy implements DropdownStrategyInterface
{

    public function getResults(Request $request): Collection
    {
        $query = Patient::query();

        if ($request->has('search')) {
            $query->where('telephone', 'LIKE', '%' . $request->get('search') . '%');
        }

        $query->select(['id', 'telephone AS label']);

        return $query->get();
    }
}
