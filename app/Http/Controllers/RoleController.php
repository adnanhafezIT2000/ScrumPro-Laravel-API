<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roles;
use App\Http\Requests\StoreRoleRequest;

class RoleController extends Controller
{

    /*****************
     * Get All Roles
    *****************/
    public function index() {

        return Roles::all();
    }

    /*******************
     * Store New Roles
    *******************/
    public function store(StoreRoleRequest $request){

        Roles::create($request->validated());

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Created New Role' ,
        ]);
    }

    /******************
     * Show Role Info
    ******************/
    public function show(Roles $role){

        return $role;
    }
}
