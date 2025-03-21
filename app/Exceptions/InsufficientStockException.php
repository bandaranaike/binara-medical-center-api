<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'message' => 'Insufficient stock for this brand',
        ], 422);
    }
}
