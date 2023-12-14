<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PermissionRole;

class PermissionRoleController extends Controller
{

    /******************************
     * Get All Permissions-Roles
    ******************************/
    public function index(){

        return PermissionRole::all();
    }

    /*******************************
     * Assign Permission To Role
    *******************************/
    public function manageAssignPermissionToRole(Request $request){

        $isAssign = PermissionRole::where([

            ['role_id' , $request->role_id] ,
            ['permission_id' , $request->permission_id]

        ])->exists();

        // delete this assign
        if($isAssign){

            $record = PermissionRole::where([

                ['role_id' , $request->role_id] ,
                ['permission_id' , $request->permission_id]

            ])->delete();

            return response()->json([
                'status'=> true ,
                'msg'=> 'Successfully Delete This Assign'
            ]);
        }
        // create this assign
        elseif(!$isAssign){

            PermissionRole::create($request->all());

            return response()->json([
                'status'=> true ,
                'msg'=> 'Successfully Create New Assign'
            ]);
        }

    }
    
}
