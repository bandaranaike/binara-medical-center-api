<?php

namespace App\Services;

use App\Models\Disease;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class DiseasesDropdownStrategy implements DropdownStrategyInterface
{
    /**
     * Get the results for the diseases dropdown.
     *
     * @param Request $request
     * @return Builder
     */
    public function getQuery(Request $request): Builder
    {
        $query = Disease::query();

        // If a search query is present, filter by the name of the disease
        if ($request->has('search')) {
            $query->where('name', 'LIKE', '%' . $request->get('search') . '%');
        }

        // Select id and name (as label) for the dropdown options
        $query->select(['id', 'name AS label']);

        return $query;
    }
}
