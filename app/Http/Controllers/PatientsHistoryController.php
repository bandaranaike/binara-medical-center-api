<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePatientsHistoryRequest;
use App\Http\Requests\UpdatePatientsHistoryRequest;
use App\Models\PatientsHistory;

class PatientsHistoryController extends Controller
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
    public function store(StorePatientsHistoryRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(PatientsHistory $patientsHistory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PatientsHistory $patientsHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientsHistoryRequest $request, PatientsHistory $patientsHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PatientsHistory $patientsHistory)
    {
        //
    }
}
