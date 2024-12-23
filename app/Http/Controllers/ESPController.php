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
     * Save the data of BPMs per second on Redis and calculate the average BPM per minute.
     * 
     * @param int $bpm
     * @return void
     */
    public function saveDataBPMonRedis(int $bpm): void {
        
        // Exibir o valor de bpm para depuração
        var_dump($bpm);

        // Salvar o último valor de bpm
        Cache::put('bpm', $bpm, 60);

        // Recupera o índice do minuto atual e incrementa para salvar a próxima entrada
        $currentMinuteIndex = Cache::get('minute_index', 1);

        // Salva os batimentos do minuto atual, criando um array para o minuto se não existir
        $minuteBPMs = Cache::get('minute_bpms', []);
        
        // Converte o índice para inteiro para evitar que seja tratado como string
        $currentMinuteIndex = (int) $currentMinuteIndex;

        // Inicializa o array para o índice do minuto, caso ainda não exista
        if (!isset($minuteBPMs[$currentMinuteIndex])) {
            $minuteBPMs[$currentMinuteIndex] = [];
        }

        // Adiciona o valor atual de BPM no minuto atual
        $minuteBPMs[$currentMinuteIndex][] = $bpm;

        // Se tivermos 4 leituras para este minuto, calculamos a média e salvamos no MySQL
        if (count($minuteBPMs[$currentMinuteIndex]) >= 4) {
            $averageBPM = array_sum($minuteBPMs[$currentMinuteIndex]) / count($minuteBPMs[$currentMinuteIndex]);

            // Obtém o mínimo e máximo do intervalo de BPMs registrados
            $maxBPM = max($minuteBPMs[$currentMinuteIndex]);
            $minBPM = min($minuteBPMs[$currentMinuteIndex]);

            // Salva a média no banco de dados (a função pode ser descomentada se já implementada)
            // $this->saveAverageBPMToMySQL($averageBPM, $maxBPM, $minBPM);

            // Incrementa o índice de minuto para o próximo grupo de batimentos
            $currentMinuteIndex++;

            // Salva o índice atualizado no cache para o próximo minuto
            Cache::put('minute_index', $currentMinuteIndex, 60);
        }

        // Salva o array atualizado de batimentos por minuto no Redis
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
