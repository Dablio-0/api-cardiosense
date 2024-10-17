<?php 

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{

    /**
     * List all users
     * 
     * [GET]
     * @Dablio-0
     * 
     * @return \Illuminate\Http\JsonResponse class of response
     * 
     */
    public function index(): JsonResponse {
        try {
            $users = User::all();

            return response()->json($users, 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retrive itself user or another user
     * 
     * [GET]
     * @Dablio-0
     * @param int $id id of user
     * @return \Illuminate\Http\JsonResponse class of response
     * 
     */
    public function retrieve($id): JsonResponse {
        try {
            $user = User::find($id);

            if ($user) {
                return response()->json([
                    'status' => true, 
                    'user' => $user
                ], 200);
            }

            return response()->json(['message' => 'User not found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user data
     * 
     * [PUT]
     * @Dablio-0
     * @param Request variabel of data from request (name, email, date_birth, etc ...)
     * @param int $id id of user
     * @return \Illuminate\Http\JsonResponse class of response
     * 
     */
    public function edit(Request $request, $id): JsonResponse {

        try {
            
            $request->validate([
                'name' => 'required|string',
                'email' => 'required|email',
                'date_birth' => 'required|date',
                'sex' => ['required', 'in' . implode(',', User::$arraySex)]
            ]);
    
            $user = User::find($id);
    
            if ($user) {
                $user->name = $request->name;
                $user->email = $request->email;
                $user->date_birth = $request->date_birth;
                $user->sex = $request->sex;

                $user->save();

                return response()->json([
                    'status' => true,
                    'message' => 'User updated',
                    'user' => $user
                ], 200);

            } else return response()->json([
                'status' => false,
                'message' => 'User not found.'
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'status' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);

        }
    }
        
    /**
     * Delete user
     * 
     * [DELETE]
     * @Dablio-0
     * @param int $id id of user
     * @return \Illuminate\Http\JsonResponse class of response
     * 
     */
    public function delete($id): JsonResponse {
        try {
            $user = User::find($id);

            if ($user) {
                $user->delete();

                return response()->json([
                    'status' => true,
                    'message' => 'User deleted'
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search user by name
     * 
     * [POST]
     * @Dablio-0
     * @param Request variabel of data from request (name)
     * @return \Illuminate\Http\JsonResponse class of response
     * 
     */
    public function search(Request $request): JsonResponse {
        try {
            $request->validate([
                'name' => 'required|string'
            ]);

            $users = User::where('name', 'like', '%' . $request->name . '%')->get();

            return response()->json([
                'status' => true,
                'users' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user password
     * 
     * To users logged
     * 
     * [PUT]
     * @Dablio-0
     * @param Request variabel of data from request (current_password, password, password_confirmation)
     * @param int $id id of user
     * @return \Illuminate\Http\JsonResponse class of response
     * 
     */
    public function userLoggedUpdatePassword(Request $request, $id): JsonResponse {
        try {
            $request->validate([
                'current_password' => 'required|string',
                'password' => 'required|string|confirmed',
                'password_confirmation' => 'required|string'
            ]);

            $user = Auth::user();

            if (Hash::check($request->current_password, $user->password)) {
                $user->password = Hash::make($request->password);
                $user->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Password updated'
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user password
     * 
     * To users not logged
     * 
     * [PUT]
     * @Dablio-0
     * @param Request variabel of data from request (password, password_confirmation)
     * @param int $id id of user
     * @return \Illuminate\Http\JsonResponse class of response
     * 
     */
    public function userNotLoggedPasswordUpdate(Request $request, $id): JsonResponse {
        try {
            $request->validate([
                'password' => 'required|string|confirmed',
                'password_confirmation' => 'required|string'
            ]);

            $user = User::find($id);

            if ($user) {
                $user->password = Hash::make($request->password);
                $user->save();

                return response()->json([
                    'status' => true,
                    'message' => 'Password updated'
                ], 200);
            }

            return response()->json([
                'status' => false,
                'message' => 'User not found'
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Server error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}