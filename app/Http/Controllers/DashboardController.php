<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Users;
use App\Models\Roles;
use App\Models\Skills;
use App\Models\Categories;
use App\Models\Projects;
use App\Models\Teams;
use App\Models\Stories;
use App\Models\Sprints;
use App\Models\DefinitionsOfDone;
use App\Models\SkillUser;
use App\Models\SprintStory;
use App\Models\Tasks;

class DashboardController extends Controller
{
    /************************
     * Get Admin Dashboard
    ************************/
    public function getAdminDashboard(){

        /**********************
         * Users Statistics
        **********************/
        $getOwnerID     = Roles::select('id')->where('name' , 'product owner')->get();
        $getMasterID    = Roles::select('id')->where('name' , 'scrum master')->get();
        $getDeveloperID = Roles::select('id')->where('name' , 'developer')->get();
        $getClientID    = Roles::select('id')->where('name' , 'client')->get();

        $countOwners     = Users::where('role_id' , $getOwnerID[0]->id)->get();
        $countMasters    = Users::where('role_id' , $getMasterID[0]->id)->get();
        $countDevelopers = Users::where('role_id' , $getDeveloperID[0]->id)->get();
        $countClients    = Users::where('role_id' , $getClientID[0]->id)->get();

        /**********************************
         * Categories & Skills Statistics
        **********************************/
        $countCategories = Categories::all();
        $countSkills     = Skills::all();

        /*************************
         * Projects Statistics
        *************************/
        $openProjects  = Projects::where('status' , 'open')->get();
        $closeProjects = Projects::where('status' , 'close')->get();

        /************************************
         * Most Ranks Masters / Developers
        ************************************/
        $mostRankDevelopers = Users::where('role_id' , $getDeveloperID[0]->id)
                                    ->orderByDesc('rank')->limit(3)->get();
        for ($i=0; $i < count($mostRankDevelopers) ; $i++) {

                if($i == 0){
                    $mostRankDevelopers[$i]->rank_color = 'gold-rank';
                } elseif($i == 1){
                    $mostRankDevelopers[$i]->rank_color = 'silver-rank';
                } elseif($i == 2){
                    $mostRankDevelopers[$i]->rank_color = 'bronze-rank';
                }
        }

        $mostRankMasters    = Users::where('role_id' , $getMasterID[0]->id)
                                    ->orderByDesc('rank')->limit(3)->get();
        for ($i=0; $i < count($mostRankMasters) ; $i++) {

                if($i == 0){
                    $mostRankMasters[$i]->rank_color = 'gold-rank';
                } elseif($i == 1){
                    $mostRankMasters[$i]->rank_color = 'silver-rank';
                } elseif($i == 2){
                    $mostRankMasters[$i]->rank_color = 'bronze-rank';
                }
        }

        return response()->json([
            'users' => [
                'count_owners'     => count($countOwners),
                'count_masters'    => count($countMasters),
                'count_developers' => count($countDevelopers),
                'count_clients'    => count($countClients)
            ] ,
            'categories_skills' => [
                'count_categories' => count($countCategories),
                'count_skills'     => count($countSkills),
            ] ,
            'all_projects' => [
                'open_projects'  => count($openProjects),
                'close_projects' => count($closeProjects)
            ] ,
            'top_three_ranked' => [
                'masters'    => $mostRankMasters ,
                'developers' => $mostRankDevelopers ,
            ]
        ]);
    }

    /*****************************************
     * Get Owner/Master/Developer Dashboard
    *****************************************/
    public function getDashboard(Request $request){

        if($request->roleName == 'product owner'){

            /**********************
             * Stories Statistics
            **********************/
            $allStories     = Stories::where('project_id' , $request->projectID)->get();
            $allStoriesToDo = Stories::where([
                                        ['project_id' , $request->projectID] ,
                                        ['status' , 'to do']
                                    ])->get();
            $allStoriesDone = Stories::where([
                ['project_id' , $request->projectID] ,
                ['status' , 'done']
            ])->get();
            $allPoints = 0; $ToDoPoints = 0; $DonePoints = 0;
            for ($i=0; $i < count($allStories) ; $i++) {

                if($allStories[$i]->status == 'to do'){

                        $ToDoPoints += $allStories[$i]->points;
                }
                else if($allStories[$i]->status == 'done'){

                        $DonePoints += $allStories[$i]->points;
                }
                $allPoints += $allStories[$i]->points;
            }

            /**********************
             * Sprints Statistics
            **********************/
            $sprints = Sprints::where('project_id' , $request->projectID)->get();

            /***********************************
             * Definitions Of Done Statistics
            ***********************************/
            $definitions = DefinitionsOfDone::where('project_id' , $request->projectID)->get();

            /***********************************
             * Project Date Statistics
            ***********************************/
            $projectDate = Projects::where('id' , $request->projectID)->get();
            $transformCreateAtProject = explode('T' , $projectDate[0]->created_at);
            $newCreatedAtProject = explode(" " , $transformCreateAtProject[0]);

            /*********************
             * Team Statistics
            *********************/
            $teamID = Projects::select('team_id')->where('id' , $request->projectID)->get();
            $getTeamInfo = Users::leftJoin('categories' , 'users.category_id' , 'categories.id')
                                ->select('users.*' , 'categories.name AS category_name')
                                ->where('team_id' , $teamID[0]->team_id)
                                ->get();
            for ($i=0; $i < count($getTeamInfo) ; $i++) {

                $skills = SkillUser::leftJoin('users' , 'skill_users.user_id' , 'users.id')
                                    ->leftJoin('skills' , 'skill_users.skill_id' , 'skills.id')
                                    ->select('skills.name AS skill_name')
                                    ->where('users.id' , $getTeamInfo[$i]->id)
                                    ->get();

                $getTeamInfo[$i]->skills = $skills;
            }
            $teamVelocity = Teams::select('velocity')->where('id' , $teamID[0]->team_id)->get();

            /*********************
             * Story In Progress
            *********************/
            $storyInProgress = Stories::where([
                ['project_id' , $request->projectID] ,
                ['status' , 'in progress']
            ])->limit(1)->get();

            if(count($storyInProgress) == 1){
                $getStorySprint = SprintStory::leftJoin('sprints' , 'sprint_stories.sprint_id' , 'sprints.id')
                                    ->select('sprints.name')
                                    ->where('sprint_stories.story_id' , $storyInProgress[0]->id)
                                    ->get();
                $tasksInStory = Tasks::leftJoin('categories' , 'tasks.category_id' , 'categories.id')
                                    ->leftJoin('skills' , 'tasks.skill_id' , 'skills.id')
                                    ->select('tasks.*' , 'categories.name AS category_name' , 'skills.name AS skill_name')
                                    ->where('tasks.story_id' , $storyInProgress[0]->id)
                                    ->get();
                $storyInProgress[0]->sprint_name = $getStorySprint[0]->name;
                $storyInProgress[0]->tasks = $tasksInStory;
            }

            return response()->json([
                'stories' => [
                    'all' => [
                        "total"  => count($allStories),
                        "points" => $allPoints
                    ] ,
                    'todo' => [
                        "total"  => count($allStoriesToDo) ,
                        "points" => $ToDoPoints
                    ] ,
                    'done' => [
                        "total"  => count($allStoriesDone) ,
                        "points" => $DonePoints
                    ]
                ] ,
                'sprints'      => count($sprints) ,
                'definitions'  => count($definitions) ,
                'project_date' => [
                    'created_date' => $newCreatedAtProject[0],
                    'planned_terminate_date' => $projectDate[0]->planned_termination_date,
                    'actual_terminate_date' => $projectDate[0]->actual_termination_date,
                ] ,
                'team' => $getTeamInfo ,
                'team_velocity' => $teamVelocity[0]->velocity ,
                'story_progress' => $storyInProgress ,
                'found_story_progress' => count($storyInProgress)
            ]);

        }
        elseif($request->roleName == 'scrum master'){

            /********************
             * Tasks Statistics
            ********************/
            $tasks = Tasks::leftJoin('stories' , 'tasks.story_id' , 'stories.id')
                            ->select('tasks.*')
                            ->where('stories.project_id' , $request->projectID)
                            ->get();
            $allTasks = 0; $ToDoTasks = 0; $DoneTasks = 0;
            for ($i=0; $i < count($tasks) ; $i++) {

                if($tasks[$i]->status == 'to do'){

                        $ToDoTasks++;
                }
                else if($tasks[$i]->status == 'done'){

                        $DoneTasks++;
                }
                $allTasks++;
            }

            /**********************
             * Sprints Statistics
            **********************/
            $sprints = Sprints::where('project_id' , $request->projectID)->get();

            /*********************
             * Team Statistics
            *********************/
            $teamID = Projects::select('team_id')->where('id' , $request->projectID)->get();
            $getTeamInfo = Users::leftJoin('categories' , 'users.category_id' , 'categories.id')
                                ->select('users.*' , 'categories.name AS category_name')
                                ->where('team_id' , $teamID[0]->team_id)
                                ->get();
            for ($i=0; $i < count($getTeamInfo) ; $i++) {

                $skills = SkillUser::leftJoin('users' , 'skill_users.user_id' , 'users.id')
                                    ->leftJoin('skills' , 'skill_users.skill_id' , 'skills.id')
                                    ->select('skills.name AS skill_name')
                                    ->where('users.id' , $getTeamInfo[$i]->id)
                                    ->get();

                $getTeamInfo[$i]->skills = $skills;
            }
            $teamVelocity = Teams::select('velocity')->where('id' , $teamID[0]->team_id)->get();

            /*********************
             * Story In Progress
            *********************/
            $storyInProgress = Stories::where([
                ['project_id' , $request->projectID] ,
                ['status' , 'in progress']
            ])->limit(1)->get();

            if(count($storyInProgress) == 1){
                $getStorySprint = SprintStory::leftJoin('sprints' , 'sprint_stories.sprint_id' , 'sprints.id')
                                    ->select('sprints.name')
                                    ->where('sprint_stories.story_id' , $storyInProgress[0]->id)
                                    ->get();
                $tasksInStory = Tasks::leftJoin('categories' , 'tasks.category_id' , 'categories.id')
                                    ->leftJoin('skills' , 'tasks.skill_id' , 'skills.id')
                                    ->select('tasks.*' , 'categories.name AS category_name' , 'skills.name AS skill_name')
                                    ->where('tasks.story_id' , $storyInProgress[0]->id)
                                    ->get();
                $storyInProgress[0]->sprint_name = $getStorySprint[0]->name;
                $storyInProgress[0]->tasks = $tasksInStory;
            }

            return response()->json([
                'tasks'   => [
                    'total' => $allTasks ,
                    'todo'  => $ToDoTasks ,
                    'done'  => $DoneTasks
                ],
                'sprints' => count($sprints) ,
                'team' => $getTeamInfo ,
                'team_velocity' => $teamVelocity[0]->velocity ,
                'story_progress' => $storyInProgress ,
                'found_story_progress' => count($storyInProgress),
            ]);

        }
        elseif($request->roleName == 'developer'){

            /*********************
             * Team Statistics
            *********************/
            $teamID = Projects::select('team_id')->where('id' , $request->projectID)->get();
            $getTeamInfo = Users::leftJoin('categories' , 'users.category_id' , 'categories.id')
                                ->select('users.*' , 'categories.name AS category_name')
                                ->where('team_id' , $teamID[0]->team_id)
                                ->get();
            for ($i=0; $i < count($getTeamInfo) ; $i++) {

                $skills = SkillUser::leftJoin('users' , 'skill_users.user_id' , 'users.id')
                                    ->leftJoin('skills' , 'skill_users.skill_id' , 'skills.id')
                                    ->select('skills.name AS skill_name')
                                    ->where('users.id' , $getTeamInfo[$i]->id)
                                    ->get();

                $getTeamInfo[$i]->skills = $skills;
            }

            $tasks = Tasks::leftJoin('categories' , 'tasks.category_id' , 'categories.id')
                                ->leftJoin('skills' , 'tasks.skill_id' , 'skills.id')
                                ->select('tasks.*' , 'categories.name AS category_name' , 'skills.name AS skill_name')
                                ->where('tasks.user_id' , $request->developerID)
                                ->get();
            
            $teamVelocity = Teams::select('velocity')->where('id' , $teamID[0]->team_id)->get();

            return response()->json([
                'team'  => $getTeamInfo ,
                'team_velocity' => $teamVelocity[0]->velocity ,
                'my_tasks' => $tasks ,
                'found_my_tasks' => count($tasks)
            ]);
        }
        else if($request->roleName == 'client'){

            /**********************
             * Stories Statistics
            **********************/
            $allStories     = Stories::where('project_id' , $request->projectID)->get();
            $allStoriesToDo = Stories::where([
                                        ['project_id' , $request->projectID] ,
                                        ['status' , 'to do']
                                    ])->get();
            $allStoriesDone = Stories::where([
                ['project_id' , $request->projectID] ,
                ['status' , 'done']
            ])->get();
            $allPoints = 0; $ToDoPoints = 0; $DonePoints = 0;
            for ($i=0; $i < count($allStories) ; $i++) {

                if($allStories[$i]->status == 'to do'){

                        $ToDoPoints += $allStories[$i]->points;
                }
                else if($allStories[$i]->status == 'done'){

                        $DonePoints += $allStories[$i]->points;
                }
                $allPoints += $allStories[$i]->points;
            }

            /**********************
             * Sprints Statistics
            **********************/
            $sprints = Sprints::where('project_id' , $request->projectID)->get();

            /***********************************
             * Project Date Statistics
            ***********************************/
            $projectDate = Projects::where('id' , $request->projectID)->get();
            $transformCreateAtProject = explode('T' , $projectDate[0]->created_at);
            $newCreatedAtProject = explode(" " , $transformCreateAtProject[0]);

            /***********************************
             * Definitions Of Done Statistics
            ***********************************/
            $definitions = DefinitionsOfDone::where('project_id' , $request->projectID)->get();

            /*********************
             * Team Statistics
            *********************/
            $teamID = Projects::select('team_id')->where('id' , $request->projectID)->get();
            $getTeamInfo = Users::leftJoin('categories' , 'users.category_id' , 'categories.id')
                                ->select('users.*' , 'categories.name AS category_name')
                                ->where('team_id' , $teamID[0]->team_id)
                                ->get();
            for ($i=0; $i < count($getTeamInfo) ; $i++) {

                $skills = SkillUser::leftJoin('users' , 'skill_users.user_id' , 'users.id')
                                    ->leftJoin('skills' , 'skill_users.skill_id' , 'skills.id')
                                    ->select('skills.name AS skill_name')
                                    ->where('users.id' , $getTeamInfo[$i]->id)
                                    ->get();

                $getTeamInfo[$i]->skills = $skills;
            }
            $teamVelocity = Teams::select('velocity')->where('id' , $teamID[0]->team_id)->get();

            /*********************
             * Story In Progress
            *********************/
            $storyInProgress = Stories::where([
                ['project_id' , $request->projectID] ,
                ['status' , 'in progress']
            ])->limit(1)->get();

            if(count($storyInProgress) == 1){
                $getStorySprint = SprintStory::leftJoin('sprints' , 'sprint_stories.sprint_id' , 'sprints.id')
                                    ->select('sprints.name')
                                    ->where('sprint_stories.story_id' , $storyInProgress[0]->id)
                                    ->get();
                $tasksInStory = Tasks::leftJoin('categories' , 'tasks.category_id' , 'categories.id')
                                    ->leftJoin('skills' , 'tasks.skill_id' , 'skills.id')
                                    ->select('tasks.*' , 'categories.name AS category_name' , 'skills.name AS skill_name')
                                    ->where('tasks.story_id' , $storyInProgress[0]->id)
                                    ->get();
                $storyInProgress[0]->sprint_name = $getStorySprint[0]->name;
                $storyInProgress[0]->tasks = $tasksInStory;
            }

            return response()->json([
                'stories' => [
                    'all' => [
                        "total"  => count($allStories),
                        "points" => $allPoints
                    ] ,
                    'todo' => [
                        "total"  => count($allStoriesToDo) ,
                        "points" => $ToDoPoints
                    ] ,
                    'done' => [
                        "total"  => count($allStoriesDone) ,
                        "points" => $DonePoints
                    ]
                ] ,
                'project_date' => [
                    'created_date' => $newCreatedAtProject[0],
                    'planned_terminate_date' => $projectDate[0]->planned_termination_date,
                    'actual_terminate_date' => $projectDate[0]->actual_termination_date,
                ] ,
                'sprints' => count($sprints) ,
                'definitions'  => count($definitions) ,
                'team' => $getTeamInfo ,
                'team_velocity' => $teamVelocity[0]->velocity ,
                'story_progress' => $storyInProgress ,
                'found_story_progress' => count($storyInProgress)
            ]);
        }
    }
}
