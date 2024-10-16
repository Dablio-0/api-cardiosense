<?php 

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{

    public function index(): JsonResponse {
        $users = User::paginate(2);

        return response()->json($users, 200);
    }
        
}