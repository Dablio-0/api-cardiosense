<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ESPController extends Controller
{

    public function testCommunicationESP() : JsonResponse {
        return response()->json(['message' => 'Comunicação com ESP32 realizada com sucesso'], 200);
    }
    
    /**
     * Get the data sent by the ESP32
     * 
     * @param Request $request Request data from ESP32
     * @return \Illuminate\Http\JsonResponse Class of Response
     */
    public function getDataESP(Request $request) : JsonResponse{
        
        // Checks if teh request has the JSON data
        if($request->has('data')) {
            $data = $request->input('data');
            $this->saveDataBPMonRedis($data);
            return response()->json(['message' => 'Dados salvos com sucesso'], 200);
        }

        return response()->json(['message' => 'Dados não encontrados'], 404);
    }

    /**
     * SAve the data of BPMs per second on Redis Database
     * 
     * @param array $data
     * @return void
     */
    public function saveDataBPMonRedis($data) : void {

        // 
    }
}
