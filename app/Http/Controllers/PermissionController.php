<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permissions;
use App\Http\Requests\StorePermissionRequest;

class PermissionController extends Controller
{

    /***********************
     * Get All Permissions
    ***********************/
    public function index(){

        return Permissions::all();
    }

    /*************************
     * Store New Permissions
    *************************/
    public function store(StorePermissionRequest $request){

        Permissions::create($request->validated());

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Created New Permission' ,
        ]);

    }
}
