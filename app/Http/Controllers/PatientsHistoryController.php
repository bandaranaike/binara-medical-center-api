<?php
namespace App\Http\Controllers;

use App\Http\Requests\StorePatientsHistoryRequest;
use App\Http\Requests\UpdatePatientsHistoryRequest;
use App\Http\Resources\PatientsHistoryResource;
use App\Models\PatientsHistory;
use Illuminate\Http\Request;

class PatientsHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $histories = PatientsHistory::all();
        return PatientsHistoryResource::collection($histories);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientsHistoryRequest $request)
    {
        $history = PatientsHistory::create($request->validated());

        return new PatientsHistoryResource($history);
    }

    /**
     * Display the specified resource.
     */
    public function show(PatientsHistory $patientsHistory)
    {
        return new PatientsHistoryResource($patientsHistory);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientsHistoryRequest $request, PatientsHistory $patientsHistory)
    {
        $patientsHistory->update($request->validated());

        return new PatientsHistoryResource($patientsHistory);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PatientsHistory $patientsHistory)
    {
        $patientsHistory->delete();

        return response()->json(null, 204);
    }
}
