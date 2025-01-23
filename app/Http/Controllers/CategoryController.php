<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;
use App\Models\Category;

class CategoryController extends Controller
{
    use CrudTrait;

    public function __construct()
    {
        $this->model = new Category();
        $this->updateRequest = new UpdateCategoryRequest();
        $this->storeRequest = new StoreCategoryRequest();
    }

}
