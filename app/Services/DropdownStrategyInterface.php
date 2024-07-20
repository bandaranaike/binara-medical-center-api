<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;

interface DropdownStrategyInterface
{
    public function getResults(Request $request): Collection;
}
