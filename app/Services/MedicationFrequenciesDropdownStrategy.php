<?php

namespace App\Services;

use App\Models\Category;
use App\Models\MedicationFrequency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class MedicationFrequenciesDropdownStrategy implements DropdownStrategyInterface
{
    /**
     * Get the results for the dropdown.
     *
     * @param Request $request
     * @return Builder
     */
    public function getQuery(Request $request): Builder
    {
        $query = MedicationFrequency::query();

        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%');
        }

        $query->select(['id', 'name AS label']);

        return $query;
    }
}
