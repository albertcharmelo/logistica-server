<?php

namespace App\Http\Controllers;

use App\Http\Resources\ReclamoLogResource;
use App\Models\ReclamoLog;
use Illuminate\Http\Request;

class ReclamoLogController extends Controller
{
    /**
     * GET /api/reclamos/{reclamo}/logs
     */
    public function index(Request $request, int $reclamoId)
    {
        $perPage = (int) $request->query('per_page', 0);

        $query = ReclamoLog::with(['user'])
            ->where('reclamo_id', $reclamoId)
            ->orderBy('id');

        if ($perPage > 0) {
            $p = $query->paginate($perPage);
            return response()->json([
                'success' => true,
                'code'    => 200,
                'data'    => [
                    'logs' => ReclamoLogResource::collection($p->items()),
                    'pagination' => [
                        'total'         => $p->total(),
                        'per_page'      => $p->perPage(),
                        'current_page'  => $p->currentPage(),
                        'last_page'     => $p->lastPage(),
                    ],
                ],
            ], 200);
        }

        $items = $query->get();
        return response()->json([
            'success' => true,
            'code'    => 200,
            'data'    => ReclamoLogResource::collection($items),
        ], 200);
    }
}
