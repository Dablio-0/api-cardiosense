<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class MonitorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Retrieve BPM data from Redis.
     *
     * @return JsonResponse
     */
    public function getDataBPMFromRedis() : JsonResponse {
        try {
            $arrayBPMSMinute = Cache::get('minute_bpms', "");
            
            // Verifica se o dado está em string JSON e, se necessário, decodifica
            if (is_string($arrayBPMSMinute)) {
                $arrayBPMSMinute = json_decode($arrayBPMSMinute, true);
            }
    
            return response()->json([
                'status' => true,
                'data' => $arrayBPMSMinute
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to retrieve BPM data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
}
