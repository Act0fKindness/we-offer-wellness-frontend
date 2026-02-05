<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use App\Models\Reservation;

class ReservationController extends Controller
{
    /**
     * Create a temporary reservation (10-minute hold).
     */
    public function hold(Request $request)
    {
        // Require an authenticated user to persist a DB reservation
        $user = Auth::user();
        if (!$user) {
            return response()->json([ 'ok' => false, 'message' => 'Unauthenticated' ], 401);
        }

        $data = $request->validate([
            'date' => ['required','date'],
            'time' => ['required','date_format:H:i'],
            'duration_minutes' => ['nullable','integer','min:5','max:480'],
        ]);

        $duration = (int)($data['duration_minutes'] ?? 60);
        $start = Carbon::createFromFormat('H:i', $data['time']);
        $end = (clone $start)->addMinutes($duration);

        $res = Reservation::create([
            'user_id' => $user->id,
            'date' => $data['date'],
            'start_time' => $start->format('H:i:s'),
            'end_time' => $end->format('H:i:s'),
            'is_confirmed' => false,
        ]);

        $expiresAt = Carbon::now()->addMinutes(10);
        return response()->json([
            'ok' => true,
            'id' => $res->id,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);
    }

    /**
     * Release an unconfirmed reservation (delete if not confirmed).
     */
    public function release(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json([ 'ok' => false, 'message' => 'Unauthenticated' ], 401);
        }

        $data = $request->validate([
            'id' => ['required','integer','min:1'],
        ]);

        $res = Reservation::where('id', $data['id'])
            ->where('user_id', $user->id)
            ->where('is_confirmed', false)
            ->first();
        if (!$res) {
            return response()->json([ 'ok' => true, 'released' => false ]);
        }
        $res->delete();
        return response()->json([ 'ok' => true, 'released' => true ]);
    }
}

