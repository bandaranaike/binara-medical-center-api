<?php

namespace App\Services;

use App\Models\Patient;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PatientDropdownStrategy implements DropdownStrategyInterface
{

    public function getQuery(Request $request): Builder
    {
        $query = Patient::query();

        if ($request->has('search')) {
            $query->where('telephone', 'LIKE', '%' . $request->get('search') . '%');
        }

        $query->select(['id', 'telephone AS label']);

        return $query;
    }
}
