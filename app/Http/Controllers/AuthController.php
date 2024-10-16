<?php 

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
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
    public function register(Request $request){
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
    public function login(Request $request)
    {
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){

            $user = Auth::user();

            $token = $user->createToken('api-token')->plainTextToken;

            return response()->json([
                'status' => true,
                'token' => $token,
                'user' => $user,
            ], 201);

        } else {
        
            return response()->json(['message' => 'Login ou senha inválidos'], 401);
        };
        
        
    }
}