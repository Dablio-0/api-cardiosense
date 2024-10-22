<?php 

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\ResetPasswordCode;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Carbon;


class AuthController extends Controller
{

    /**
     * Register a new user (Create a new user)
     * 
     * [POST]
     * @Dablio-0
     * @param Request variabel of data from request (name, email, date_birth, etc ...)
     * @return \Illuminate\Http\JsonResponse class of response
     *
     */
    public function register(Request $request) : JsonResponse {
        try {
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'date_birth' => ['required', 'date', 'date_format:d/m/Y'],
                'sex' => ['required', 'in:' . implode(',', User::$arraySex)],
                'password' => 'required|string|confirmed',
                'password_confirmation' => 'required|string'
            ]);

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->date_birth = Carbon::parse($request->date_birth)->format('Y-m-d');
            $user->sex = $request->sex;
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json([
                'status' => true,
                'message' => 'User created'
            ], 201);

        } catch (ValidationException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Validation error', 
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Server error', 
                'error' => $e->getMessage()
            ], 500);

        }
    }

    /**
     * Login a user (Create a new token)
     * 
     * [POST]
     * @Dablio-0
     * @param Request variabel of data from request (email, password)
     * @return \Illuminate\Http\JsonResponse class of response type
     * 
     */
    public function login(Request $request) : JsonResponse {

        try {
            if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $user = Auth::user();
            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'token' => $token,
                'user' => $user,
            ], 201);

            } else {

            return response()->json([
                'status' => false,
                'message' => 'Login ou senha inválidos'], 401);
            }

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Erro no servidor',
                'error' => $e->getMessage()], 500);
        }
    }
        

    /**
     * Perfoms the logout of the user
     * 
     * Revoke the tokens associated with the user
     * 
     * [POST]
     * @Dablio-0
     * @param User $user the user to logout
     * @return \Illuminate\Http\JsonResponse class of response type
     */
    public function logout(User $user) : JsonResponse {

        try {

            // Verifcar se é esse usuário que está logado
            if (Auth::user()->id != $user->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized'], 401);
            }

            $user->tokens()->delete();

            return response()->json([
                'status' => true,
                'message' => 'Logout realizado com sucesso'
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Erro no servidor',
                'error' => $e->getMessage()
            ], 500);

        }
    }
    
    /**
     * Send a password reset code to the user's e-mail.
     *
     * This method validates the user's email, checks if the user exists,
     * generates a 6-digit reset code, stores it in cache with a 10-minute expiration,
     * and sends the code to the user's email.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance containing the user's email.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the operation.
     */
    public function sendPasswordResetCode(Request $request) : JsonResponse
    {
        try {
            // Valida o e-mail
            $request->validate(['email' => 'required|email']);
            
            // Verifica se o usuário existe
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email and user not found'], 404);
            }

            // Gera um código de 6 dígitos
            $resetCode = random_int(100000, 999999);

            // Gera a URL para resetar a senha
            $url = 'http://localhost:8080/reset-password?email=' . $user->email . '&reset_code=' . $resetCode;

            // Recupera os códigos de redefinição de senha armazenados no cache
            $resetCodes = Cache::get('password_reset_codes_' . $user->id, []);

            // Adiciona o novo código à lista
            $resetCodes[] = [
                'reset_code' => $resetCode,
                'created_at' => now()
            ];

            // Limita o número de códigos armazenados, por exemplo, 5 códigos
            if (count($resetCodes) > 5) {
                array_shift($resetCodes); // Remove o código mais antigo
            }

            // Armazena o array de códigos no cache (expira em 10 minutos)
            Cache::put('password_reset_codes_' . $user->id, $resetCodes, 600);

            // Envia o e-mail com o código
            Mail::to($user->email)->send(new ResetPasswordCode($resetCode, $url));

            return response()->json([
                'status' => true,
                'message' => 'Reset code sent to email',
                'email' => $user->email
            ]);

        } catch (ValidationException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Verify the reset code for password recovery.
     *
     * This method validates the user's email and reset code, checks if the user exists,
     * and verifies if the reset code is valid (stored in cache).
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance containing the user's email and reset code.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating whether the reset code is valid.
     */
    public function verifyResetCode(Request $request) : JsonResponse
    {
        try {
            // Validação do e-mail e do código
            $request->validate([
                'email' => 'required|email',
                'reset_code' => 'required|int',
            ]);

            // Busca o usuário pelo e-mail
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email and user not found'
                ], 404);
            }

            // Recupera os códigos de redefinição de senha armazenados no cache
            $resetCodes = Cache::get('password_reset_codes_' . $user->id, []);

            // Se não houver códigos armazenados ou a lista estiver vazia
            if (empty($resetCodes)) {
                return response()->json([
                    'status' => false,
                    'message' => 'No reset codes found',
                    'email' => $user->email
                ], 400);
            }

            // Pega o último código gerado
            $lastResetCode = end($resetCodes);

            // Verifica se o código enviado é o último código gerado
            if ($lastResetCode['reset_code'] != $request->reset_code) {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid or expired reset code'
                ], 400);
            }

            // Se o código for válido, atualiza o campo 'last_verified_at' e salva no cache
            $lastResetCode['last_verified_at'] = now();
            $lastResetCode['verified'] = true;
            $resetCodes[key($resetCodes)] = $lastResetCode;

            Cache::put('password_reset_codes_' . $user->id, $resetCodes, 600);  // Atualiza o cache

            return response()->json([
                'status' => true,
                'message' => 'Reset code verified',
                'email' => $user->email
            ], 200);

        } catch (ValidationException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Reset the user's password after verifying the reset code.
     *
     * This method validates the user's email and new password, updates the user's password, 
     * and removes the reset code from cache.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance containing the user's email, new password, and password confirmation.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the password reset operation.
     */
    public function resetPassword(Request $request) : JsonResponse
    {
        try {
            // Valida os campos de e-mail e senha
            $request->validate([
                'email' => 'required|email',
                'password' => 'required|string|confirmed',
                'password_confirmation' => 'required|string'
            ]);

            // Busca o usuário pelo e-mail
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'Email and user not found'
                ], 404);
            }

            // Atualiza a senha do usuário
            $user->password = Hash::make($request->password);
            $user->save();

            // Remove o código de reset do cache (opcional, se ainda não foi removido)
            Cache::forget('password_reset_' . $user->id);

            return response()->json([
                'status' => true,
                'message' => 'Password successfully updated'
            ], 200);

        } catch (ValidationException $e) {

            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);

        }
    }


}