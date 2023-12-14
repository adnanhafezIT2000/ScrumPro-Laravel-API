<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Mail\UserStoreSendPassowrd;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\StoreUserRequest;
use App\Models\Users;
use App\Models\Roles;
use App\Models\SkillUser;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /*********************
     * Get All Users
    *********************/
    public function index(Request $request){

        if( $request->category == '' ){

            $users = DB::table('users')
                ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->leftJoin('categories', 'users.category_id', '=', 'categories.id')
                ->select('users.*', 'roles.name AS role_name' , 'categories.name AS category_name')
                ->orderByDesc('users.created_at')
                ->where('users.full_name' , 'like' , '%' . $request->search . '%')
                ->where('users.status' , 'like' , '%' . $request->status . '%')
                ->where('roles.name' , 'like' , '%' . $request->role . '%')
                ->paginate(6);

        }
        else{

            $users = DB::table('users')
                ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->leftJoin('categories', 'users.category_id', '=', 'categories.id')
                ->select('users.*', 'roles.name AS role_name' , 'categories.name AS category_name')
                ->orderByDesc('users.created_at')
                ->where('users.full_name' , 'like' , '%' . $request->search . '%')
                ->where('users.status' , 'like' , '%' . $request->status . '%')
                ->where('roles.name' , 'like' , '%' . $request->role . '%')
                ->where('categories.name' , 'like' , '%' . $request->category . '%')
                ->paginate(6);

        }
        return $users;
    }

   /********************
     * Store New User
    *******************/
    public function store(StoreUserRequest $request){

        if(Users::create($request->validated())){

            Mail::to($request->email)
            ->send(new UserStoreSendPassowrd($request->email , $request->password , $request->full_name));

            if( isset($request->skills) && count($request->skills) > 0 ){

                $userData = Users::where('email' , $request->email)->first();
                $userID = $userData->id;
                $skills = $request->skills;

                $skillsCount = count($skills);
                for ($i=0; $i < $skillsCount; $i++) {
                    
                    SkillUser::create([
                        'user_id'  => $userID ,
                        'skill_id' => $skills[$i]
                    ]);
                }
            }

            return response()->json([
                'status' => true ,
                'msg' => 'Successfully Created New User' ,
            ]);
        }
    }

   /*********************
     * Show User Info
    *********************/
    public function show(Users $user){

        $rowUser = DB::table('users')
                ->leftJoin('roles', 'users.role_id', '=', 'roles.id')
                ->leftJoin('categories', 'users.category_id', '=', 'categories.id')
                ->select('users.*', 'roles.name AS role_name' , 'categories.name AS category_name')
                ->orderByDesc('users.created_at')
                ->where('users.id' , $user->id)
                ->get();

        $skillUser = DB::table('skills')
                ->leftJoin('skill_users', 'skills.id', '=', 'skill_users.skill_id')
                ->select('skills.name')
                ->where('skill_users.user_id' , $user->id)
                ->get();

        return response()->json([
            'data'   => $rowUser ,
            'skills' => $skillUser
        ]);

    }

    /******************
     * Destroy User
    ******************/
    public function destroy(Users $user){

        $user->delete();

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Deleted user' ,
        ]);
    }

    /************************
     * Change User Status
    ************************/
    public function changeUserStatus (Request $request){

        $data = Users::where('id',$request->id)->first();
        $status=$data->status;

        if($status==0){
            Users::where('id',$request->id)->update([
                'status'=>'1',
            ]);
        }else{
            Users::where('id',$request->id)->update([
                'status'=>'0',
            ]);
        }

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully changed New User' ,
        ]);
    }

    /*************************
     * Get All Product Owner
    *************************/
    public function getOwners(){

        $ownerID = Roles::select('id')->where('name' , 'product owner')->get();
        $getOwners = Users::where('role_id' , $ownerID[0]->id)->get();

        return $getOwners;
    }
}

