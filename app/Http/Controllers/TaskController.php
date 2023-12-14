<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Tasks;
use App\Models\Stories;
use App\Models\SprintStory;
use App\Models\Users;
use App\Models\Sprints;
use App\Models\Categories;
use App\Models\Skills;
use App\Models\Roles;
use App\Models\Dependence;
use App\Models\Projects;
use App\Models\SkillUser;
use App\Models\AcceptanceCritiria;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreTaskRequest;
use Illuminate\Support\Collection;

class TaskController extends Controller
{

    /****************************
     * Get All Tasks For Story
    ****************************/
    public function getTasksForStory(Request $request){

        /* Get Story Info */
        $storyInfo = Stories::where('id' , $request->storyID)->get();
        $storySprint  = SprintStory::where('story_id' , $request->storyID)->get();
        $getSprintName = Sprints::where('id' , $storySprint[0]->sprint_id)->get();
        $getAcceptanceCritiria = AcceptanceCritiria::where('story_id' , $request->storyID)
        ->orderByDesc('created_at')->get();
        $getStoriesCount = Stories::where('project_id' , $storyInfo[0]->project_id)->get();

        $storyInfo[0]->acceptance_critiria = $getAcceptanceCritiria;
        $storyInfo[0]->sprint_name = $getSprintName[0]->name;
        $storyInfo[0]->rank = $storySprint[0]->story_rank;
        $storyInfo[0]->stories_count = count($getStoriesCount);

        /* Get Tasks For Story */
        $tasks = Tasks::leftJoin('categories' , 'tasks.category_id' , 'categories.id')
        ->leftJoin('skills' , 'tasks.skill_id' , 'skills.id')
        ->select('tasks.*' , 'categories.name AS category_name' , 'skills.name AS skill_name')
        ->where('tasks.story_id' , $request->storyID)
        ->get();

        for ($i=0; $i < count($tasks) ; $i++) {

            // Dependence Tasks
            $foundDependenceTask = Dependence::where('task_id' , $tasks[$i]->id)
            ->orWhere('blocking_by' , $tasks[$i]->id)->get();

            if(count($foundDependenceTask) == 0){

                $tasks[$i]->blocking_by_tasks = 'none';
                $tasks[$i]->blocked_tasks = 'none';
                $tasks[$i]->is_blocked = false;

            } else{

                // get dependence task
                $blockingBy = new Collection();
                $blocked    = new Collection();
                for ($j=0; $j <count($foundDependenceTask) ; $j++) {

                    if($tasks[$i]->id == $foundDependenceTask[$j]->task_id){

                        $blockingBy->push($foundDependenceTask[$j]->blocking_by);

                    } elseif($tasks[$i]->id == $foundDependenceTask[$j]->blocking_by){

                        $blocked->push($foundDependenceTask[$j]->task_id);
                    }
                }
                $tasks[$i]->blocking_by_tasks = $blockingBy;
                $tasks[$i]->blocked_tasks     = $blocked;

                // Check if the task is blocked or no
                if(count($blockingBy) == 0){

                    $tasks[$i]->is_blocked = false;

                } else{

                    $isBlocked = 0;
                    for ($k=0; $k < count($blockingBy) ; $k++) {

                        $taskInfo = Tasks::where('id' , $blockingBy[$k])->get();
                        if($taskInfo[0]->status != 'done'){
                            $isBlocked++;
                        }

                    }

                    if($isBlocked == 0){
                        $tasks[$i]->is_blocked = false;
                    } else{
                        $tasks[$i]->is_blocked = true;
                    }
                }
            }

            if($tasks[$i]->user_id != NULL){
                $user = Users::select('full_name','rank','avatar')
                ->where('id' , $tasks[$i]->user_id)
                ->get();

                $tasks[$i]->user_found = true;
                $tasks[$i]->full_name = $user[0]->full_name;
                $tasks[$i]->user_rank = $user[0]->rank;
                $tasks[$i]->avater = $user[0]->avater;

            } else{
                $tasks[$i]->user_found = false;
            }
        }

        return response()->json([
            'story' => [
                'data' => $storyInfo ,
                'status' => $storyInfo[0]->status
            ] ,
            'tasks' => [
                'count_tasks' => count($tasks) ,
                'data' => $tasks
            ]
        ]);
    }

    /****************
     * Store Task
    ****************/
    public function store(StoreTaskRequest $request){

        Tasks::create($request->validated());

        return response()->json([
            'status' => true ,
            'msg' => 'Successfully Created New Task' ,
        ]);
    }

    /***************
     * Show Task
    ***************/
    public function show(Tasks $task){

        // get task category name
        $category = Categories::where('id' , $task->category_id)->get();
        $task->category_name = $category[0]->name;

        // get skill name
        $skill    = Skills::where('id' , $task->skill_id)->get();
        $task->skill_name    = $skill[0]->name;

        // get user info
        if($task->user_id != NULL){
            $user = Users::select('full_name','rank','avatar')
            ->where('id' , $task->user_id)
            ->get();

            $task->user_found = true;
            $task->full_name = $user[0]->full_name;
            $task->user_rank = $user[0]->rank;
            $task->avater = $user[0]->avater;

        } else{
            $task->user_found = false;
        }

        // get comments
        $comments = DB::table('comments')
        ->leftJoin('users', 'comments.user_id', '=', 'users.id')
        ->select('comments.*', 'users.full_name' , 'users.avatar' , 'users.gender')
        ->where('task_id' , $task->id)
        ->get();
        $task->comments_count  = count($comments);
        $task->comments = $comments;

        return $task;
    }

    /************************
     * Update Task Title
    ************************/
    public function updateTaskTitle(Request $request , string $id){
        DB::table('tasks')
            ->where('id', $id)
            ->update(['title' => $request->title]);

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Updated Task Title'
        ]);
    }

    /****************************
     * Update Task Description
    ****************************/
    public function updateTaskDescription(Request $request , string $id){
        DB::table('tasks')
            ->where('id', $id)
            ->update(['description' => $request->description]);

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Updated Task Description'
        ]);
    }

    /******************
     * Destroy Task
    ******************/
    public function destroy(Tasks $task){

        $task->delete();
        return response()->json([
            'status' => true ,
            'msg' => 'Successfully Deleted Task' ,
        ]);
    }

    /******************************
     * Get Tasks Box For Project
    ******************************/
    public function getTasksBox(Request $request){

        // Get Story Status (in progress)
        $story = Stories::where([
                                ['project_id' , $request->projectID] ,
                                ['status' , 'in progress']
                            ])->get();

        if(count($story) != 0){

            // Get Tasks For Story
            $tasks = Tasks::leftJoin('categories' , 'tasks.category_id' , 'categories.id')
            ->leftJoin('skills' , 'tasks.skill_id' , 'skills.id')
            ->select('tasks.*' , 'categories.name AS category_name' , 'skills.name AS skill_name')
            ->where('tasks.story_id' , $story[0]->id)
            ->get();

            for ($i=0; $i < count($tasks) ; $i++) {

                // Dependence Tasks
                $foundDependenceTask = Dependence::where('task_id' , $tasks[$i]->id)
                ->orWhere('blocking_by' , $tasks[$i]->id)->get();

                if(count($foundDependenceTask) == 0){

                    $tasks[$i]->blocking_by_tasks = 'none';
                    $tasks[$i]->blocked_tasks = 'none';
                    $tasks[$i]->is_blocked = false;

                } else{

                    // get dependence task
                    $blockingBy = new Collection();
                    $blocked    = new Collection();
                    for ($j=0; $j <count($foundDependenceTask) ; $j++) {

                        if($tasks[$i]->id == $foundDependenceTask[$j]->task_id){

                            $blockingBy->push($foundDependenceTask[$j]->blocking_by);

                        } elseif($tasks[$i]->id == $foundDependenceTask[$j]->blocking_by){

                            $blocked->push($foundDependenceTask[$j]->task_id);
                        }
                    }
                    $tasks[$i]->blocking_by_tasks = $blockingBy;
                    $tasks[$i]->blocked_tasks     = $blocked;

                    // Check if the task is blocked or no
                    if(count($blockingBy) == 0){

                        $tasks[$i]->is_blocked = false;

                    } else{

                        $isBlocked = 0;
                        for ($k=0; $k < count($blockingBy) ; $k++) {

                            $taskInfo = Tasks::where('id' , $blockingBy[$k])->get();
                            if($taskInfo[0]->status != 'done'){
                                $isBlocked++;
                            }

                        }

                        if($isBlocked == 0){
                            $tasks[$i]->is_blocked = false;
                        } else{
                            $tasks[$i]->is_blocked = true;
                        }
                    }
                }

                if($tasks[$i]->user_id != NULL){
                    $user = Users::select('full_name','rank','avatar','gender')
                    ->where('id' , $tasks[$i]->user_id)
                    ->get();

                    $tasks[$i]->user_found = true;
                    $tasks[$i]->full_name = $user[0]->full_name;
                    $tasks[$i]->user_rank = $user[0]->rank;
                    $tasks[$i]->avater = $user[0]->avater;
                    $tasks[$i]->gender = $user[0]->gender;

                } else{
                    $tasks[$i]->user_found = false;
                }
            }

            return response()->json([
                'found_tasks' => true ,
                'tasks' => $tasks ,
                'tasks_count' => count($tasks)
            ]);

        }
        else{

            return response()->json([
                'found_tasks' => false ,
                'tasks' => [] ,
                'tasks_count' => 0
            ]);
        }
    }

    /***********************
     * Select User Tasks
    ***********************/
    public function selectUserTasks(Request $request){

        // Get User Category & Skills
        $userCategory = Users::select('category_id')
                            ->where('id' , $request->userID)
                            ->get();
        $userskills   = SkillUser::select('skill_id')
                            ->where('user_id' , $request->userID)
                            ->get();

        // Get Task Category & skill
        $taskInfo = Tasks::select('category_id' , 'skill_id')
                        ->where('id' , $request->taskID)
                        ->get();

        // Get User Rank
        $userRank = Users::select('rank')
                    ->where('id' , $request->userID)
                    ->get();

        // Get Task Level
        $taskLevel = Tasks::select('level')
                        ->where('id' , $request->taskID)
                        ->get();


        // Match Check
        $skillCheck = false;
        if($userCategory[0]->category_id == $taskInfo[0]->category_id){

            for ($i=0; $i < count($userskills) ; $i++) {

                if($userskills[$i]->skill_id == $taskInfo[0]->skill_id){

                    $skillCheck = true;
                }
            }

            if($skillCheck){

                if($userRank[0]->rank >= $taskLevel[0]->level){

                    DB::table('tasks')
                    ->where('id', $request->taskID)
                    ->update(['user_id' => $request->userID , 'status'=>'in progress']);

                    return response()->json([
                        "status" => true ,
                        "msg"    => "Successfully Selected Task"
                    ]);

                } else{

                    return response()->json([
                        "status" => false ,
                        "msg"    => "Faild Select, Your Rank That Does Not Match The Level Task"
                    ]);
                }

            } else{

                return response()->json([
                    "status" => false ,
                    "msg"    => "Faild Select, Your Skills That Does Not Match The Skill Task"
                ]);
            }

        } else{

            return response()->json([
                "status" => false ,
                "msg"    => "Faild Select, Your Category That Does Not Match The Category Task"
            ]);
        }
    }

    /****************************
     * Developer Finished Task
    ****************************/
    public function finishedTask(Request $request){

        /* Transform Task Status To Done */
        DB::table('tasks')
            ->where([
                ['id', $request->taskID] ,
                ['user_id' => $request->developerID]
            ])->update(['status'=>'done']);

        /* Calculate Developer Rank */
        $getDeveloperRank = Users::select('rank')->where('id' , $request->developerID)->get();
        $getTaskLevel     = Tasks::select('level')->where('id' , $request->taskID)->get();

        $calcPlusRank     = ($getTaskLevel[0]->level * 2) / 100;
        $newDeveloperRank = $getDeveloperRank[0]->rank + $calcPlusRank;

        /* Update Developer Rank */
        DB::table('users')
            ->where('id', $request->developerID)
            ->update(['rank'=>$newDeveloperRank]);

        /* Check Tasks In Story Are Done ?? */
        $getStoryID = Tasks::select('story_id')->where([
                                ['id' , $request->taskID] ,
                                ['user_id' , $request->developerID]
                            ])->get();

        $getALlTasksInStory = Tasks::where('story_id' , $getStoryID[0]->story_id)->get();

        $notDoneTasks = 0;
        for ($i=0; $i < count($getALlTasksInStory) ; $i++) {

            if($getALlTasksInStory[$i]->status != 'done'){

                $notDoneTasks++;
            }
        }

        /* If No Done Tasks == Update Story To Done */
        if($notDoneTasks == 0){

            DB::table('stories')
            ->where([
                ['id', $getStoryID[0]->story_id] ,
            ])->update(['status'=>'done']);

            // Calculation Scrum Master Rank
            $calcAverageTasksLevel = 0;
            for ($i=0; $i < count($getALlTasksInStory) ; $i++) {

                $calcAverageTasksLevel += $getALlTasksInStory[$i]->level;
            }
            $calcAverageTasksLevel = $calcAverageTasksLevel / count($getALlTasksInStory);
            $calcPlusRankMaster = ($calcAverageTasksLevel * count($getALlTasksInStory)) / 100;

            // Update Scrum Master Rank
            $roleMasterID  = Roles::select('id')->where('name' ,'scrum master')->get();
            $teamID        = Users::select('team_id')->where('id' , $request->developerID)->get();
            $getMasterRank = Users::select('rank')->where([
                                        ['role_id', $roleMasterID[0]->id] ,
                                        ['team_id', $teamID[0]->team_id] ,
                                    ])->get();
            $newMasterRank = $getMasterRank[0]->rank + $calcPlusRankMaster;

            DB::table('users')
            ->where([
                ['role_id', $roleMasterID[0]->id] ,
                ['team_id', $teamID[0]->team_id] ,
            ])->update(['rank'=>$newMasterRank]);
        }
    }
}
