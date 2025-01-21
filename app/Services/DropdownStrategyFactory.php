<?php

namespace App\Services;

use InvalidArgumentException;

class DropdownStrategyFactory
{
    public function make(string $type): DropdownStrategyInterface
    {
        return match ($type) {
            'allergies' => new AllergiesDropdownStrategy(),
            'brands' => new BrandsDropdownStrategy(),
            'categories' => new CategoriesDropdownStrategy(),
            'diseases' => new DiseasesDropdownStrategy(),
            'doctors' => new DoctorsDropdownStrategy(),
            'drugs' => new DrugsDropdownStrategy(),
            'hospitals' => new HospitalsDropdownStrategy(),
            'medicines' => new MedicinesDropdownStrategy(),
            'opd-doctors' => new OPDDoctorsDropdownStrategy(),
            'patients' => new PatientsDropdownStrategy(),
            'roles' => new RolesDropdownStrategy(),
            'services' => new ServicesDropdownStrategy(),
            'specialties' => new SpecialtiesDropdownStrategy(),
            'suppliers' => new SuppliersDropdownStrategy(),
            'users' => new UsersDropdownStrategy(),
            default => throw new InvalidArgumentException("Invalid type: {$type}")
        };
    }
}
