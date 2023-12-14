<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreProjectRequest;
use App\Models\Projects;
use App\Mail\UserStoreSendPassowrd;
use App\Models\Roles;
use App\Models\Stories;
use App\Models\Users;
use App\Models\Sprints;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ProjectController extends Controller
{

    /*********************
     * Get All projects
    *********************/
    public function index(Request $request){

        $masterID  = Roles::select('id')->where('name' , 'scrum master')->get();

        if( $request->ownerID == '' ){

            $allProjects = DB::table('projects')
            ->leftJoin('users' , 'projects.owner_id' , 'users.id')
            ->leftJoin('users AS masters' , 'projects.team_id' , 'masters.team_id')
            ->select('projects.*' ,
                    'users.full_name AS owner_name' , 'users.avatar AS owner_avater' ,
                    'masters.full_name AS master_name' , 'masters.avatar AS master_avater')
            ->where('masters.role_id' , $masterID[0]->id)
            ->orderByDesc('projects.created_at')

            ->where('projects.name' , 'like' , '%' . $request->search . '%')
            ->where('projects.status' , 'like' , '%' . $request->status . '%')

            ->paginate(6);

        }
        else{

            $allProjects = DB::table('projects')
            ->leftJoin('users' , 'projects.owner_id' , 'users.id')
            ->leftJoin('users AS masters' , 'projects.team_id' , 'masters.team_id')
            ->select('projects.*' ,
                    'users.full_name AS owner_name' , 'users.avatar AS owner_avater' ,
                    'masters.full_name AS master_name' , 'masters.avatar AS master_avater')
            ->where('masters.role_id' , $masterID[0]->id)
            ->orderByDesc('projects.created_at')

            ->where('projects.name' , 'like' , '%' . $request->search . '%')
            ->where('projects.status' , 'like' , '%' . $request->status . '%')
            ->where('projects.owner_id' , $request->ownerID )

            ->paginate(6);
        }

        return $allProjects;
    }

    /***************************************
     * Get All projects To Product Owner
    ***************************************/
    public function getAllProjectsOwner(Request $request){

        $masterID  = Roles::select('id')->where('name' , 'scrum master')->get();

        $allProjects = DB::table('projects')
        ->leftJoin('users' , 'projects.team_id' , 'users.team_id')
        ->select('projects.*' ,
                 'users.full_name AS master_name' , 'users.avatar AS master_avater')
        ->where([
            ['users.role_id' , $masterID[0]->id] ,
            ['projects.owner_id' , $request->ownerID]
        ])
        ->orderByDesc('projects.created_at')

        ->where('projects.name' , 'like' , '%' . $request->search . '%')
        ->where('projects.status' , 'like' , '%' . $request->status . '%')
        ->where('projects.owner_id' , $request->ownerID )

        ->paginate(6);

        return $allProjects;
    }

    /**************************************
     * Get All projects To Scrum Master
    **************************************/
    public function getAllProjectsMaster(Request $request){

        // Get Master ID
        $scrumMasterID  = Roles::select('id')->where('name' , 'scrum master')->get();

        // Get Team ID
        $getTeamID = Users::where('id' , $request->masterID)->get();
        $teamID = $getTeamID[0]->team_id;

        // Get All Project Where Team ID = $team_id
        $allProjects = DB::table('projects')
        ->where([
            ['team_id' , $teamID]
        ])
        ->orderByDesc('created_at')
        ->paginate(6);

        // Get Name Product Owner Projects
        for ($i=0; $i < count($allProjects) ; $i++) {

            $getOwnerName = Users::where('id' , $allProjects[$i]->owner_id)->get();

            $ownerName = $getOwnerName[0]->full_name;

            $ownerAvatar = $getOwnerName[0]->avatar;

            $allProjects[$i]->owner_name = $ownerName;

            $allProjects[$i]->owner_avater = $ownerAvatar;
        }

        return $allProjects;
    }

    /*************************************
     * Get All projects To Developers
    *************************************/
    public function getAllProjectsDeveloper(Request $request){

        // Get Developer ID
        $developerID  = Roles::select('id')->where('name' , 'developer')->get();

        // Get Team ID
        $getTeamID = Users::where('id' , $request->developerID)->get();
        $teamID = $getTeamID[0]->team_id;

        // Get All Project Where Team ID = $team_id
        $allProjects = DB::table('projects')
        ->where([
            ['team_id' , $teamID]
        ])
        ->orderByDesc('created_at')
        ->paginate(6);

        // Get Name Product Owner Projects
        for ($i=0; $i < count($allProjects) ; $i++) {

            $getOwnerName = Users::where('id' , $allProjects[$i]->owner_id)->get();

            $ownerName = $getOwnerName[0]->full_name;

            $ownerAvatar = $getOwnerName[0]->avatar;

            $allProjects[$i]->owner_name = $ownerName;

            $allProjects[$i]->owner_avater = $ownerAvatar;
        }

        return $allProjects;
    }

    /*************************************
     * Get All projects To Clients
    *************************************/
    public function getAllProjectsClient(Request $request){

        // Get All Project Where Team ID = $team_id
        $allProjects = DB::table('projects')
        ->where([
            ['client_id' , $request->clientID]
        ])
        ->orderByDesc('created_at')
        ->paginate(6);

        // Get Name Product Owner Projects
        for ($i=0; $i < count($allProjects) ; $i++) {

            $getOwnerName = Users::where('id' , $allProjects[$i]->owner_id)->get();

            $ownerName = $getOwnerName[0]->full_name;

            $ownerAvatar = $getOwnerName[0]->avatar;

            $allProjects[$i]->owner_name = $ownerName;

            $allProjects[$i]->owner_avater = $ownerAvatar;
        }

        return $allProjects;
    }

    /************************
     * Store New Projects
    ************************/
    public function store(StoreProjectRequest $request){

        $getClientRoleID = Roles::select('id')->where('name' , 'client')->get();
        $checkClientEamil = Users::where([
                                ['email' , $request->client_email] ,
                                ['role_id' , $getClientRoleID[0]->id] ,
                            ])->exists();

        if($checkClientEamil){

            $getClientInfo = Users::select('id')->where('email' , $request->client_email)->get();
            $createdAt = Carbon::now()->toDateTimeString();
            DB::table('projects')->insert([
                "owner_id"    => $request->owner_id ,
                "team_id"     => $request->team_id ,
                "client_id"   => $getClientInfo[0]->id ,
                "name"        => $request->name ,
                "description" => $request->description,
                "budget"      => $request->budget ,
                "planned_termination_date" => $request->planned_termination_date ,
                "created_at" => $createdAt
            ]);
            return response()->json([

                'status' => true ,
                'msg' => 'Successfully Created New Project' ,
            ]);


        } else{

            DB::table('users')->insert([
                "role_id"    => $getClientRoleID[0]->id ,
                "full_name"  => $request->client_name ,
                "email"      => $request->client_email ,
                "password"   => "client12345" ,
                "phone"      => "none",
                "address"    => "none" ,
                "birthday"   => "1900-01-01" ,
                "gender"     => "none"
            ]);
            Mail::to($request->client_email)
            ->send(new UserStoreSendPassowrd($request->client_email , "client12345" , $request->client_name));

            $getClientInfo = Users::select('id')->where('email' , $request->client_email)->get();
            DB::table('projects')->insert([
                "owner_id"    => $request->owner_id ,
                "team_id"     => $request->team_id ,
                "client_id"   => $getClientInfo[0]->id ,
                "name"        => $request->name ,
                "description" => $request->description,
                "budget"      => $request->budget ,
                "planned_termination_date" => $request->planned_termination_date ,
            ]);
            return response()->json([

                'status' => true ,
                'msg' => 'Successfully Created New Project' ,
            ]);
        }
    }

    /***********************
     * Show Project Info
    ***********************/
    public function show(Projects $project){

        // Transform (created_at) Project
        $transformCreateAtProject = explode('T' , $project->created_at);
        $newCreatedAtProject = explode(" " , $transformCreateAtProject[0]);
        $project->project_created = $newCreatedAtProject[0];

        // Get Product Owner
        $getOwner = Users::where('id' , $project->owner_id)->get();

        // Get Stories
        $count_todo_stories = 0;
        $count_progress_stories = 0;
        $count_done_stories = 0;
        $getStories = Stories::where('project_id' , $project->id)->get();
        for ($i=0; $i < count($getStories) ; $i++) {
            if($getStories[$i]->status == 'to do'){
                $count_todo_stories++;
            } elseif($getStories[$i]->status == 'in progress'){
                $count_progress_stories++;
            } elseif($getStories[$i]->status == 'done'){
                $count_todo_stories++;
            }
        }

        // Get Sprints
        $sprints = Sprints::where('project_id' , $project->id)->get();

        // Get Team
        $team = Users::leftJoin('categories' , 'users.category_id' , 'categories.id')
                    ->select('users.*' , 'categories.name AS category_name')
                    ->where('team_id' , $project->team_id)
                    ->get();

        return response()->json([
            'project_info'  => $project ,
            'product_owner' => $getOwner[0] ,
            'stories' => [
                'total_count_stories'    => count($getStories),
                'count_todo_stories'     => $count_todo_stories,
                'count_progress_stories' => $count_progress_stories,
                'count_done_stories'     => $count_done_stories
            ] ,
            'sprints' => $sprints ,
            'team' => $team
        ]);
    }

    /*****************
     * Close Project
    *****************/
    public function closeProject(Request $request){

        $nowDateTime = Carbon::now();
        $actualTerminateDate = explode(" " , $nowDateTime);
        $actualTerminateDate = $actualTerminateDate[0];

        DB::table('projects')
        ->where('id', $request->projectID)
        ->update(['status' => 'close' , 'actual_termination_date'=> $actualTerminateDate]);

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Close project'
        ]);
    }

}
