<?php

namespace App\Services;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class RolesDropdownStrategy implements DropdownStrategyInterface
{
    /**
     * Get the results for the allergies dropdown.
     *
     * @param Request $request
     * @return Builder
     */
    public function getQuery(Request $request): Builder
    {
        $query = Role::query();

        // If a search query is present, filter by the name of the allergy
        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%')
                ->orWhere('key', 'LIKE', '%' . $request->get('search') . '%');
        }

        // Select id and name (as label) for the dropdown options
        $query->select(['id', 'name AS label']);

        return $query;
    }
}
