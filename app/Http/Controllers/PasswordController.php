<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;

class PasswordController extends Controller
{

    public function changePassword(Request $request){

        Users::find($request->id)
        ->update(['password' => $request->newPassword]);
    }
}
