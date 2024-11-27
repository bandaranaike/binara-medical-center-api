<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DoctorsTicketController extends Controller
{
    /**
     * Generate a new ticket for a doctor.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function createTicket(Request $request)
    {
        // Validate the request to ensure doctor_id is present
        $validatedData = $request->validate([
            'doctor_id' => 'required|exists:doctors,id', // Ensure doctor exists
        ]);

        $doctorId = $validatedData['doctor_id'];
        $today = date('Y-m-d'); // Get today's date

        // Generate the next ticket number in a transaction to avoid race conditions
        $newTicket = DB::transaction(function () use ($doctorId, $today) {
            // Lock the row for the last ticket issued today for the doctor
            $lastTicket = DoctorTicket::where('doctor_id', $doctorId)
                ->where('date', $today)
                ->lockForUpdate()
                ->orderBy('ticket_number', 'desc')
                ->first();

            // Determine the next ticket number
            $newTicketNumber = $lastTicket ? $lastTicket->ticket_number + 1 : 1;

            // Create the new ticket
            return DoctorTicket::create([
                'doctor_id' => $doctorId,
                'ticket_number' => $newTicketNumber,
                'date' => $today,
            ]);
        });

        // Return the new ticket
        return response()->json([
            'ticket_number' => $newTicket->ticket_number,
            'message' => 'Ticket created successfully',
        ], 201);
    }
}
