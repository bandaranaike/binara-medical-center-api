<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDoctorAvailabilityRequest;
use App\Http\Requests\UpdateDoctorAvailabilityRequest;
use App\Models\DoctorAvailability;

class DoctorAvailabilityController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDoctorAvailabilityRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(DoctorAvailability $doctorAvailability)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DoctorAvailability $doctorAvailability)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorAvailabilityRequest $request, DoctorAvailability $doctorAvailability)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DoctorAvailability $doctorAvailability)
    {
        //
    }
}
