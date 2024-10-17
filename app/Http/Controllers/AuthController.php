<?php 

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

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
                'date_birth' => 'required|date',
                'sex' => ['required', 'in:' . implode(',', User::$arraySex)],
                'password' => 'required|string|confirmed',
                'password_confirmation' => 'required|string'
            ]);

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->date_birth = $request->date_birth;
            $user->sex = $request->sex;
            $user->password = Hash::make($request->password);
            $user->save();

            return response()->json(['message' => 'User created'], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
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
            return response()->json(['message' => 'Login ou senha inválidos'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro no servidor', 'error' => $e->getMessage()], 500);
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
                return response()->json(['message' => 'Unauthorized'], 401);
            }

            $user->tokens()->delete();

            return response()->json(['message' => 'Logout realizado com sucesso'], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erro no servidor', 'error' => $e->getMessage()], 500);
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
                return response()->json(['message' => 'Email and user not found'], 404);
            }

            // Gera um código de 6 dígitos
            $resetCode = Str::random(6);
            
            // Armazena o código em cache com uma expiração (ex: 10 minutos)
            Cache::put('password_reset_' . $user->id, $resetCode, 600); // 600 segundos = 10 minutos

            Mail::to($user->email)->send(new ResetPassword($resetCode));

            return response()->json(['message' => 'Reset code sent']);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Reset the user's password using a reset code.
     *
     * This method validates the user's input, checks if the user exists,
     * verifies the reset code, updates the user's password, and removes the reset code from cache.
     *
     * @param \Illuminate\Http\Request $request The HTTP request instance containing the user's email, 
     * reset code, and new password.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the result of the operation.
     */
    public function resetPasswordWithCode(Request $request) : JsonResponse
    {
        try {
            $request->validate([
                'email' => 'required|email',
                'reset_code' => 'required|string',
                'password' => 'required|string|confirmed',
                'password_confirmation' => 'required|string'
            ]);

            // Encontra o usuário pelo e-mail
            $user = User::where('email', $request->email)->first();
            if (!$user) {
                return response()->json(['message' => 'Email and user not found'], 404);
            }

            // Recupera o código de reset armazenado
            $storedResetCode = Cache::get('password_reset_' . $user->id);

            // Verifica se o código é válido
            if (!$storedResetCode || $storedResetCode !== $request->reset_code) {
                return response()->json(['message' => 'Invalid or expired reset code'], 400);
            }

            // Atualiza a senha do usuário
            $user->password = Hash::make($request->password);
            $user->save();

            // Remove o código de reset do cache
            Cache::forget('password_reset_' . $user->id);

            return response()->json(['message' => 'Password successfully updated'], 200);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation error', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Server error', 'error' => $e->getMessage()], 500);
        }
    }

}