<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function show($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'status' => 'success',
            'message' => 'User details retrieved successfully',
            'data' => $user,
        ], 200);
    }

    // Other methods like update, delete, etc., can be added as needed
}
