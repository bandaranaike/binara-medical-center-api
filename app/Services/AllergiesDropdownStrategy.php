<?php

namespace App\Services;

use App\Models\Allergy;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class AllergiesDropdownStrategy implements DropdownStrategyInterface
{
    /**
     * Get the results for the allergies dropdown.
     *
     * @param Request $request
     * @return Collection
     */
    public function getResults(Request $request): Collection
    {
        $query = Allergy::query();

        // If a search query is present, filter by the name of the allergy
        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%');
        }

        // Select id and name (as label) for the dropdown options
        $query->select(['id', 'name AS label']);

        return $query->get();
    }
}
