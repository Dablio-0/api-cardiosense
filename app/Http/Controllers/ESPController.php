<?php

namespace App\Http\Controllers;

use App\Models\UserBPMHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function generateEspToken(Request $request)
    {
        $user = User::where('email', 'esp32@exemplo.com')->first();

        if (!$user) {
            return response()->json(['status' => false, 'message' => 'Usuário ESP32 não encontrado.'], 404);
        }

        $token = $user->createToken('ESP32-Access-Token')->plainTextToken;

        return response()->json(['status' => true, 'token' => $token]);
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
            if($request->has('bpm')) {
                
                $bpm = $request->bpm;

                $this->saveDataBPMonRedis($bpm);
                return response()->json([
                    'message' => 'Dados salvos no redis com sucesso!',
                    'data' => [
                        'bpm' => $bpm ?? null
                    ],
            ], 200);
            
            } else {
                return response()->json(['message' => 'Dados não encontrados ou não salvos.'], 404);
            }

        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro no Servidor!'], 500);
        }
    }

    /**
     * Save the data of BPMs per second on Redis and calculate the average BPM per minute
     * 
     * @param array $data
     * @return void
     */
    public function saveDataBPMonRedis($bpm) : void {

        var_dump($bpm);

        Cache::put('bpm', $bpm, 5);

        // Save BPMs in an array for minute average calculation
        $minuteBPMs = Cache::get('minute_bpms', []);
        $minuteBPMs[] = $bpm;

        // If we have 8 BPM readings (one per 15 seconds per minute in 2 minutes), calculate the average and save to MySQL
        if (count($minuteBPMs) >= 4) {
            $averageBPM = array_sum($minuteBPMs) / count($minuteBPMs);

            // Get min and max bpm on this interval
            $maxBPM = max($minuteBPMs);
            $minBPM = min($minuteBPMs);

            // $this->saveAverageBPMToMySQL($averageBPM, $maxBPM, $minBPM);
            // // Reset the array for the next minute
            // $minuteBPMs = [];
        }

        // Save the updated array back to Redis
        Cache::put('minute_bpms', $minuteBPMs, 60);
    }

    /**
     * Save the average BPM to MySQL database   

     * 
     * @param float $averageBPM Average BPM per minute
     * @param float $maxBPM Max BPM per minute
     * @param float $minBPM Min BPM per minute
     * @return void
     */
    public function saveAverageBPMToMySQL($averageBPM, $maxBPM, $minBPM) : void {

        // Save the average BPM from Redis data to MySQL
        if(Auth::check()) {

            var_dump("Authcheck");
            $user = Auth::user();

            var_dump($user->name);

            $averageRegistry = new UserBPMHistory();

            $averageRegistry->bpm_interval_average = $averageBPM;
            $averageRegistry->bpm_interval_max = $maxBPM;
            $averageRegistry->bpm_interval_min = $minBPM;
            $averageRegistry->user_id = $user->id;
            
            // Check if the user is related in the familyRelationships table
            $familyRelationship = DB::table('familyRelationships')
                ->where('user_id', $user->id)
                ->orWhere('user_related_id', $user->id)
                ->first();

            if ($familyRelationship) {
                $averageRegistry->family_id = $familyRelationship->family_id;
            }

            $averageRegistry->save();
        } else {
            // Check for token in localStorage
            $token = request()->bearerToken();
            if ($token) {
                $user = DB::table('users')->where('api_token', $token)->first();
                if ($user) {
                    $averageRegistry = new UserBPMHistory();

                    $averageRegistry->bpm_interval_average = $averageBPM;
                    $averageRegistry->bpm_interval_max = $maxBPM;
                    $averageRegistry->bpm_interval_min = $minBPM;
                    $averageRegistry->user_id = $user->id;
                    
                    // Check if the user is related in the familyRelationships table
                    $familyRelationship = DB::table('familyRelationships')
                        ->where('user_id', $user->id)
                        ->orWhere('user_related_id', $user->id)
                        ->first();

                    if ($familyRelationship) {
                        $averageRegistry->family_id = $familyRelationship->family_id;
                    }

                    $averageRegistry->save();
                }
            }
        }
    }
}
