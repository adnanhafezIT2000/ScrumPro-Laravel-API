<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Teams;
use App\Models\Users;
use App\Models\Roles;
use App\Models\Projects;
use App\Http\Requests\StoreTeamRequest;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{

    /*****************
     * Get All Teams
    *****************/
    public function index(){

        $allTeams = Teams::all();

        $masterID    = Roles::select('id')->where('name' , 'scrum master')->get();
        $developerID = Roles::select('id')->where('name' , 'developer')->get();

        for ($i=0; $i < count($allTeams) ; $i++) {

            // Get master team
            $master_team = Users::where('role_id' , $masterID[0]->id)
                           ->where('team_id' , $allTeams[$i]->id)->get();

            $allTeams[$i]->master = $master_team;

            // Get developers team
            $developers_team = Users::where('role_id' , $developerID[0]->id)
                               ->where('team_id' , $allTeams[$i]->id)->get();

            $allTeams[$i]->developers = $developers_team;

            // Get count of develoeprs in team
            $allTeams[$i]->developers_count = count($developers_team);

            // Get team working or not working
            $workingTeam = Projects::where('team_id' , $allTeams[$i]->id)
                           ->where('status' , 'open')->exists();

            if($workingTeam){
                $allTeams[$i]->working = true;
            } else{
                $allTeams[$i]->working = false;
            }

        }

        return $allTeams;
    }

    /*********************************************
     * Get Scrum Master And Developer From Team
    *********************************************/
    public function getMastersAndDevelopers(){

        $masterID    = Roles::select('id')->where('name' , 'scrum master')->get();
        $developerID = Roles::select('id')->where('name' , 'developer')->get();

        $getMasters = Users::where([
            ['role_id' , '=' , $masterID[0]->id] ,
            ['team_id' , '=' , null]
        ])->get();

        $getDevelopers = DB::table('users')
            ->leftJoin('categories', 'users.category_id', '=', 'categories.id')
            ->select('users.*' , 'categories.name AS category_name')
            ->where([
                ['users.role_id' , $developerID[0]->id] ,
                ['team_id' , '=' , null]
            ])->get();


        for ($i=0; $i < count($getDevelopers) ; $i++) {

            $skillUser = DB::table('skills')
                ->leftJoin('skill_users', 'skills.id', '=', 'skill_users.skill_id')
                ->select('skills.name')
                ->where('skill_users.user_id' , $getDevelopers[$i]->id)
                ->get();

            $getDevelopers[$i]->skills = $skillUser;
        }

        return response()->json([
            "masters"    => $getMasters ,
            "developers" => $getDevelopers ,
        ]);
    }

    /*******************
     * Store New Teams
    *******************/
    public function store(StoreTeamRequest $request){

        if(Teams::create($request->validated())){

            // Fetch teamID
            $teamID =  Teams::orderByDesc('id')->first();
            $teamID = $teamID->id;

            // Update scrum master team_id
            Users::find($request->master_id)
            ->update(['team_id' => $teamID]);

            // Update developers team_id
            for ($i=0; $i < count($request->developers_id) ; $i++) {

                Users::find($request->developers_id[$i])
                ->update(['team_id' => $teamID]);
            }

        }
        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Created New Team' ,
        ]);
    }

    /********************************
     * Get Teams To Create Project
    ********************************/
    public function getTeamsToCreateProject(){

        $allTeams = Teams::all();

        $getTeams = array();

        for ($i=0; $i < count($allTeams) ; $i++) {

            $teamNotWorkingNever = Projects::where('team_id' , $allTeams[$i]->id)->exists();

            $teamWorkingButNowNotWorking = Projects::where([
                ['team_id' , $allTeams[$i]->id] ,
                ['status' , 'close']
            ])->exists();

            if(!$teamNotWorkingNever){

                $masterID = Roles::select('id')->where('name' , 'scrum master')->get();

                $allTeams[$i]->members_count = Users::where([
                    ['team_id' , $allTeams[$i]->id] ,
                    ['role_id' , '<>' , $masterID[0]->id]
                ])->count();

                $allTeams[$i]->master = Users::where([
                    ['team_id' , $allTeams[$i]->id] ,
                    ['role_id' , $masterID[0]->id]
                ])->get();


                array_push($getTeams , $allTeams[$i]);

            } else if($teamWorkingButNowNotWorking){

                $masterID = Roles::select('id')->where('name' , 'scrum master')->get();

                $allTeams[$i]->members_count = Users::where([
                    ['team_id' , $allTeams[$i]->id] ,
                    ['role_id' , '<>' , $masterID[0]->id]
                ])->count();

                $allTeams[$i]->master = Users::where([
                    ['team_id' , $allTeams[$i]->id] ,
                    ['role_id' , $masterID[0]->id]
                ])->get();

                array_push($getTeams , $allTeams[$i]);
            }
        }

        return response()->json([
            "teams" => $getTeams ,
            "count_teams" => count($getTeams)
        ]);
    }
}
