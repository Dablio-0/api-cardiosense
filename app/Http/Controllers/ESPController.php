<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ESPController extends Controller
{

    /**
     * Test the communication with ESP32 using GET method
     * 
     * @return \Illuminate\Http\JsonResponse Class of Response
     */
    public function testCommunicationESPGET() : JsonResponse{
        return response()->json(['message' => 'Comunicação com ESP32 realizada com sucesso'], 200);

    }

    /**
     * Test the communication with ESP32 using POST method
     * 
     * @return \Illuminate\Http\JsonResponse Class of Response
     */
    public function testCommunicationESPPOST() : JsonResponse{
        return response()->json(['message' => 'Comunicação com ESP32 realizada com sucesso'], 200);
    
    }
    
    /**
     * Get the data sent by the ESP32
     * 
     * [POST (from ESP32)]
     * 
     * @param Request $request Request data from ESP32
     * @return \Illuminate\Http\JsonResponse Class of Response
     */
    public function getDataESP(Request $request) : JsonResponse{
        
        try {
            // Checks if teh request has the JSON data
            if($request->has('data')) {
                $data = $request->input('data');
                $this->saveDataBPMonRedis($data);
                return response()->json(['message' => 'Dados salvos com sucesso!'], 200);
            } else {
                return response()->json(['message' => 'Dados não encontrados ou não salvos.'], 404);
            }

        } catch (\Expection $e) {
            return response()->json(['message' => 'Erro no Servidor!'], 500);
        }
    }

    /**
     * Save the data of BPMs per second on Redis Database
     * 
     * @param array $data
     * @return void
     */
    public function saveDataBPMonRedis($data) : void {

        
    }
}
