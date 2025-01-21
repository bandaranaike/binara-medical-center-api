<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Traits\CrudTrait;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;

class RoleController extends Controller
{
    use CrudTrait;

    public function __construct()
    {
        $this->model = new Role();
        $this->updateRequest = new UpdateRoleRequest();
        $this->storeRequest = new StoreRoleRequest();
    }
}
