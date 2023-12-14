<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Roles;
use App\Models\Users;
use App\Models\SkillUser;
use App\Models\Categories;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{

    /*****************
     * Admin Login
    *****************/
    public function adminLogin(Request $request){

        $adminRoleID = Roles::select('id')->where('name' , 'super admin')->get();

        $existsEmail = Users::where([
                            ['email'    , $request->email] ,
                            ['password' , $request->password] ,
                            ['role_id'  , $adminRoleID[0]->id] ,
                            ['status' , 1]
                       ])->exists();

        if($existsEmail){

            $adminInfo = Users::where('email' , $request->email)->get();
            $adminInfo[0]->role_name = 'super admin';

            $permissions = DB::table('permissions')
            ->leftJoin('permission_roles' , 'permissions.id' , 'permission_roles.permission_id')
            ->select('permissions.name')
            ->where('permission_roles.role_id' , $adminRoleID[0]->id)
            ->get();

            $permissionsArray = array();

            for ($i=0; $i < count($permissions); $i++) {

                array_push($permissionsArray , $permissions[$i]->name);
            }

            return response()->json([
                'login'       => true ,
                'msg'         => 'Login Successfully' ,
                'user'        => $adminInfo ,
                'permissions' => $permissionsArray
            ]);

        }
        else{

            return response()->json([
                'login' => false ,
                'msg' => 'Login Faild, Please verify your email or password'
            ]);
        }
    }

    /*****************
     * Members Login
    *****************/
    public function memebersLogin(Request $request){

        $adminRoleID = Roles::select('id')->where('name' , 'super admin')->get();

        $existsEmail = Users::where([
                            ['email'    , $request->email] ,
                            ['password' , $request->password] ,
                            ['role_id' , '<>' , $adminRoleID[0]->id] ,
                            ['status' , 1]
                       ])->exists();

        if($existsEmail){

            $userInfo = Users::where('email' , $request->email)->get();

            $userRoleID = $userInfo[0]->role_id;

            $roleName = Roles::select('name')->where('id' , $userRoleID)->get();

            $userInfo[0]->role_name = $roleName[0]->name;

            if($userInfo[0]->role_name == 'developer'){

                $getSkills = SkillUser::leftJoin('skills' , 'skill_users.skill_id' , 'skills.id')
                            ->select('skills.name')
                            ->where('skill_users.user_id' , $userInfo[0]->id)
                            ->get();

                $getCategory = Categories::select('name')->where('id' , $userInfo[0]->category_id)->get();
                $userInfo[0]->category = $getCategory[0]->name;
                $userInfo[0]->skills = $getSkills;
            }

            $permissions = DB::table('permissions')
            ->leftJoin('permission_roles' , 'permissions.id' , 'permission_roles.permission_id')
            ->select('permissions.name')
            ->where('permission_roles.role_id' , $userRoleID)
            ->get();

            $permissionsArray = array();

            for ($i=0; $i < count($permissions); $i++) {

                array_push($permissionsArray , $permissions[$i]->name);
            }

            return response()->json([
                'login'       => true ,
                'msg'         => 'Login Successfully' ,
                'user'        => $userInfo ,
                'permissions' => $permissionsArray ,
            ]);

        }
        else{

            return response()->json([
                'login' => false ,
                'msg' => 'Login Faild, Please verify your email or password'
            ]);
        }
    }
}
