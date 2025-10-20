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
            'doctors-all' => new AllDoctorsDropdownStrategy(),
            'doctors-schedules' => new DoctorSchedulesDropdownStrategy(),
            'drugs' => new DrugsDropdownStrategy(),
            'hospitals' => new HospitalsDropdownStrategy(),
            'medication_frequencies' => new MedicationFrequenciesDropdownStrategy(),
            'medicines' => new MedicinesDropdownStrategy(),
            'opd-doctors' => new OPDDoctorsDropdownStrategy(),
            'patients' => new PatientsDropdownStrategy(),
            'pharmacy-brands' => new PharmacyBrandsDropdownStrategy(),
            'roles' => new RolesDropdownStrategy(),
            'services' => new ServicesDropdownStrategy(),
            'specialties' => new SpecialtiesDropdownStrategy(),
            'suppliers' => new SuppliersDropdownStrategy(),
            'users' => new UsersDropdownStrategy(),
            'user-bills' => new UsersBillsDropdownStrategy(),
            default => throw new InvalidArgumentException("Invalid type: $type")
        };
    }
}
