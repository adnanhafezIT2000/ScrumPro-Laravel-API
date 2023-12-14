<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Stories;
use App\Models\SprintStory;
use App\Models\Sprints;
use App\Models\Tasks;
use App\Models\AcceptanceCritiria;
use App\Http\Requests\StoreStoriesRequest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class StoriesController extends Controller
{

    /************************
     * Get Project Backlog
    ************************/
    public function backlog(Request $request){

        if($request->from != '' && $request->to != ''){

            $allStories = Stories::orderByDesc('id')
                ->where('project_id' , $request->projectID)
                ->where('title' , 'like' , '%' . $request->title . '%')
                ->where('status' , 'like' , '%' . $request->status . '%')
                ->whereBetween('points' , [$request->from , $request->to])
                ->get();

        }
        elseif($request->from == '' && $request->to == ''){

            $allStories = Stories::orderByDesc('id')
                ->where('project_id' , $request->projectID)
                ->where('title' , 'like' , '%' . $request->title . '%')
                ->where('status' , 'like' , '%' . $request->status . '%')
                ->get();

        }
        elseif($request->from != '' && $request->to == ''){

            $allStories = Stories::orderByDesc('id')
                ->where('project_id' , $request->projectID)
                ->where('title' , 'like' , '%' . $request->title . '%')
                ->where('status' , 'like' , '%' . $request->status . '%')
                ->where('points' , '>=' , $request->from)
                ->get();

        }
        elseif($request->from == '' && $request->to != ''){

            $allStories = Stories::orderByDesc('id')
                ->where('project_id' , $request->projectID)
                ->where('title' , 'like' , '%' . $request->title . '%')
                ->where('status' , 'like' , '%' . $request->status . '%')
                ->where('points' , '<=' , $request->to)
                ->get();
        }

        $allPoints = 0; $ToDoPoints = 0; $ProgressPoints = 0; $DonePoints = 0;

        for ($i=0; $i < count($allStories) ; $i++) {

            if($allStories[$i]->status == 'to do'){

                    $ToDoPoints += $allStories[$i]->points;

            }
            else if($allStories[$i]->status == 'in progress'){

                    $ProgressPoints += $allStories[$i]->points;

            }
            else if($allStories[$i]->status == 'done'){

                    $DonePoints += $allStories[$i]->points;
            }
            $allPoints += $allStories[$i]->points;
        }

        return response()->json([
            "backlog" => $allStories ,
            "total_stories" => count($allStories) ,
            "points" => [
                "all"=> $allPoints ,
                "todo"=>$ToDoPoints ,
                "in_progress" => $ProgressPoints ,
                "done" => $DonePoints
            ]
        ]);
    }

    /****************
     * Show Story
    ****************/
    public function show(Stories $story){
        $storySprint  = SprintStory::where('story_id' , $story->id)->get();

        if(count($storySprint) == 0){

            $story->sprint = "none";
            return $story;

        } else{

            $getSprintName = Sprints::where('id' , $storySprint[0]->sprint_id)->get();
            $story->sprint = $getSprintName[0]->name;
            $story->status_sprint = $getSprintName[0]->status;
            return $story;
        }
    }

    /************************************
     * Store Story (only story title)
    ************************************/
    public function store(StoreStoriesRequest $request){
        Stories::create($request->validated());

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Created New Story' ,
        ]);
    }

    /***********************
     * Update Story Title
    ***********************/
    public function updateStoryTitle(Request $request , string $id){

        DB::table('stories')
            ->where('id', $id)
            ->update(['title' => $request->title]);

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Updated Story Title'
        ]);
    }

    /****************************
     * Update Story Description
    ****************************/
    public function updateStoryDescription(Request $request , string $id){

        DB::table('stories')
            ->where('id', $id)
            ->update(['description' => $request->description]);

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Updated Story Description'
        ]);
    }

    /***********************
     * Update Story points
    ***********************/
    public function updateStoryPoint(Request $request , string $id){

        DB::table('stories')
            ->where('id', $id)
            ->update(['points' => $request->points]);

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Updated Story Points'
        ]);
    }

    /*************************
     * Update Story Priority
    *************************/
    public function updateStoryPriority(Request $request , string $id){

        DB::table('stories')
            ->where('id', $id)
            ->update(['priority' => $request->priority]);

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Updated Story priority'
        ]);
    }

    /******************
     * Destroy Story
    ******************/
    public function destroy(Stories $story){

        $story->delete();

        return response()->json([
            "status" => true ,
            "msg" => 'Successfully Deleted Story'
        ]);
    }

    /**********************
     * Get Stories Board
    **********************/
    public function getStoriesBoard(Request $request){

        $todo = Stories::where([
            ['status','=', 'to do'] ,
            ['project_id' , '=' , $request->projectID]
        ])->get();
        $progress = Stories::where([
            ['status' , 'in progress'] ,
            ['project_id' , '=' , $request->projectID]
        ])->get();
        $done = Stories::where([
            ['status' , 'done'] ,
            ['project_id' , '=' , $request->projectID]
        ])->get();

        return response()->json([
            "todo" => $todo ,
            "progress" => $progress ,
            "done" =>  $done,
            "todo_count" => count($todo)
        ]);
    }

    /*********************
     * Start Story Work
    *********************/
    public function startStoryWork(Request $request){

        // Get Rank To The Story To Start
        $storyRank = SprintStory::select('story_rank')
                                ->where([
                                    ['sprint_id' , $request->sprintID] ,
                                    ['story_id' , $request->storyID]
                                ])->get();

        // Get Stories Rank And Status Story Is To Do
        $storiesRank = SprintStory::leftJoin('stories' , 'sprint_stories.story_id' , 'stories.id')
                                  ->select('sprint_stories.story_rank')
                                  ->where([
                                    ['sprint_stories.sprint_id' , $request->sprintID] ,
                                    ['sprint_stories.story_id' , '<>' , $request->storyID] ,
                                    ['stories.status' , 'to do']
                                  ])->get();

        // Check Error For Story Start
        $errorMsg = '';

        $checkRank = 0;
        for ($i=0; $i < count($storiesRank) ; $i++) {

            if($storyRank[0]->story_rank > $storiesRank[$i]->story_rank){

                $checkRank++;
                $errorMsg = 'Faild Start, There Are Stories To Need Start Before';
            }
        }

        if($checkRank == 0){

            $storiesStatus = Stories::leftJoin('sprint_stories' , 'stories.id' , 'sprint_stories.story_id')
                                    ->select('stories.status')
                                    ->where([
                                        ['sprint_stories.sprint_id' , $request->sprintID] ,
                                    ])->get();
            $checkStatus = 0;
            for ($i=0; $i < count($storiesStatus) ; $i++) {

                if($storiesStatus[$i]->status == 'in progress'){

                    $checkStatus++;
                    $errorMsg = 'Faild Start, There Is Story In Progress';
                }
            }
        }

        if($checkRank == 0 && $checkStatus == 0){

            $checkTasks = Tasks::where('story_id' , $request->storyID)->exists();
            if($checkTasks == 0){

                $errorMsg = 'Faild Start, Story Tasks Should Be Added';
            }
        }



        if($errorMsg != ''){
            return response()->json([
                "status" => false ,
                "msg" => $errorMsg
            ]);

        } else{

            $getStoryPoint = Stories::select('points')->where('id', $request->storyID)->get();

            $nowDateTime = Carbon::now();
            $nowDateTime->addDays($getStoryPoint[0]->points);
            $terminateDate = explode(" " , $nowDateTime);
            $terminateDate = $terminateDate[0];

            DB::table('stories')
                ->where('id', $request->storyID)
                ->update(['status' => 'in progress' , 'terminate_date' => $terminateDate]);

            return response()->json([
                "status" => true ,
                "msg" => "Successfully Start Story To Work"
            ]);
        }
    }
}
