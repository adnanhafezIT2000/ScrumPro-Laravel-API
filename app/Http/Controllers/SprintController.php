<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sprints;
use App\Models\Stories;
use App\Models\SprintStory;
use App\Models\Meetings;
use App\Models\Tasks;
use App\Models\Projects;
use App\Models\Teams;
use App\Http\Requests\StoreSprintRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SprintController extends Controller
{

    /* Get Sprints For Project */
    public function getSprintsForProject(Request $request){

        /* Check Story (in progress) In Sprint Backlog Is Time Finished ??? */
        $getSprintInProgress = Sprints::select('id' , 'end_date')->where([
                                        ['status' , 'in progress'] ,
                                        ['project_id' , $request->projectID]
                                    ])->get();

        if(count($getSprintInProgress) > 0){

            $getStoryInProgressForSprint = Stories::where([
                                            ['status' , 'in progress'] ,
                                            ['project_id' , $request->projectID]
                                        ])->get();
            if(count($getStoryInProgressForSprint) > 0){

                $nowDateTime = Carbon::now();
                $nowDateTime = $nowDateTime->toDateTimeString();
                $nowDate = explode(" " , $nowDateTime);
                $nowDate = $nowDate[0];

                if($getStoryInProgressForSprint[0]->terminate_date == $nowDate){

                    DB::table('stories')
                        ->where([
                            ['id', $getStoryInProgressForSprint[0]->id] ,
                            ['project_id', $getStoryInProgressForSprint[0]->project_id]
                        ])->update(['status' => 'not complete']);

                    $getTasks = Tasks::leftJoin('sprint_stories' , 'tasks.story_id' , 'sprint_stories.story_id')
                                        ->select('tasks.*')
                                        ->where([
                                            ['sprint_stories.sprint_id' , $getSprintInProgress[0]->id] ,
                                            ['tasks.status' , '<>' , 'done'] ,
                                            ['tasks.story_id' , $getStoryInProgressForSprint[0]->id]
                                        ])->get();
                    for ($i=0; $i < count($getTasks) ; $i++) {

                        DB::table('tasks')
                            ->where([
                                ['id', $getTasks[$i]->id] ,
                            ])->update(['status' => 'not complete']);

                        $getUserRank = Users::select('rank')->where('id' , $getTasks[$i]->user_id)->get();
                        $calcSubUserRank = ($getTasks[$i]->level * 2) / 100;
                        $newUserRank = $getUserRank[0]->rank - $calcSubUserRank;

                        DB::table('users')
                            ->where([
                                ['id', $getTasks[$i]->user_id] ,
                            ])->update(['rank' => $newUserRank]);
                    }
                }
            }

            /* Check Sprint Is Done ?? */
            for ($i=0; $i < count($getSprintInProgress) ; $i++) {

                $storiesStatus = SprintStory::leftJoin('stories' , 'sprint_stories.story_id' , 'stories.id')
                            ->select('stories.status')
                            ->where('sprint_stories.sprint_id' , $getSprintInProgress[$i]->id)
                            ->get();

                $checkStories = 0;
                for ($i=0; $i < count($storiesStatus) ; $i++) {

                    if($storiesStatus[$i]->status == 'to do' || $storiesStatus[$i]->status == 'in progress' || $storiesStatus[$i]->status == 'not complete'){

                        $checkStories++;
                    }
                }

            }
            if($checkStories == 0){
                DB::table("sprints")
                    ->where([
                        ['id', $getSprintInProgress[0]->id] ,
                    ])->update(['status' => 'done']);

                /* Update Team velocity */
                $getDoneStoriesPoints = Stories::select('points')->where([
                                            ['project_id' , $request->projectID] ,
                                            ['status' , 'done'] ,
                                        ])->get();
                $getDoneSprints = Sprints::where([
                                            ['project_id' , $request->projectID] ,
                                            ['status' , 'done'] ,
                                        ])->get();
                $calcSumStoriesPoints = 0;
                for ($i=0; $i < count($getDoneStoriesPoints) ; $i++) {

                    $calcSumStoriesPoints += $getDoneStoriesPoints[$i]->points;
                }
                $teamVelocity = $calcSumStoriesPoints / count($getDoneSprints);
                $getTeamID = Projects::select('team_id')->where('id' , $request->projectID)->get();
                DB::table("teams")
                        ->where([
                            ['id', $getTeamID[0]->$team_id] ,
                        ])->update(['velocity' => $teamVelocity]);
            }

            /* Check Sprint Is Not Complete (if date)... */
            $nowDateTime2 = Carbon::now();
            $nowDateTime2 = $nowDateTime2->toDateTimeString();
            $nowDate2 = explode(" " , $nowDateTime2);
            $nowDate2 = $nowDate2[0];

            if($getSprintInProgress[0]->end_date == $nowDate2){

                DB::table('sprints')
                    ->where([
                        ['id', $getSprintInProgress[0]->id] ,
                    ])->update(['status' => 'not complete']);

                $stories = SprintStory::leftJoin('stories' , 'sprint_stories.story_id' , 'stories.id')
                                ->select('stories.*')
                                ->where('sprint_stories.sprint_id' , $getSprintInProgress[0]->id)
                                ->get();
                for ($i=0; $i < count($stories) ; $i++) {
                    if($stories[$i]->status == 'to do' || $stories[$i]->status == 'in progress'){
                        DB::table('stories')
                            ->where([
                                ['id', $stories[$i]->id] ,
                            ])->update(['status' => 'not complete']);
                    }
                }
                $getTasks = Tasks::leftJoin('sprint_stories' , 'tasks.story_id' , 'sprint_stories.story_id')
                                ->select('tasks.*')
                                ->where([
                                    ['sprint_stories.sprint_id' , $getSprintInProgress[0]->id] ,
                                    ['tasks.status' , '<>' , 'done']
                                ])->get();
                for ($i=0; $i < count($getTasks) ; $i++) {
                    DB::table('tasks')
                            ->where([
                                ['id', $getTasks[$i]->id] ,
                            ])->update(['status' => 'not complete']);
                    $getUserRank2 = Users::select('rank')->where('id' , $getTasks[$i]->user_id)->get();
                    $calcSubUserRank2= ($getTasks[$i]->level * 2) / 100;
                    $newUserRank2 = $getUserRank2[0]->rank - $calcSubUserRank2;

                    DB::table('users')
                        ->where([
                            ['id', $getTasks[$i]->user_id] ,
                        ])->update(['rank' => $newUserRank2]);
                }
                /* Update Team velocity */
                $getDoneStoriesPoints = Stories::select('points')->where([
                    ['project_id' , $request->projectID] ,
                    ['status' , 'done'] ,
                ])->get();
                $getDoneSprints = Sprints::where([
                                    ['project_id' , $request->projectID] ,
                                    ['status' , 'done'] ,
                                ])->get();
                $calcSumStoriesPoints = 0;
                for ($i=0; $i < count($getDoneStoriesPoints) ; $i++) {

                $calcSumStoriesPoints += $getDoneStoriesPoints[$i]->points;
                }
                $teamVelocity = $calcSumStoriesPoints / count($getDoneSprints);
                $getTeamID = Projects::select('team_id')->where('id' , $request->projectID)->get();
                DB::table("teams")
                        ->where([
                            ['id', $getTeamID[0]->$team_id] ,
                        ])->update(['velocity' => $teamVelocity]);
            }
        }

        /* Sprints */
        $sprints = Sprints::where('project_id' , $request->projectID)
                    ->orderByDesc('created_at')->get();

        for ($i=0; $i < count($sprints) ; $i++) {

            $start_date = Carbon::parse($sprints[$i]->strat_date);
            $sprints[$i]->strat_date = $start_date->format('d') . ' ' . $start_date->format('F');

            $end_date = Carbon::parse($sprints[$i]->end_date);
            $sprints[$i]->end_date = $end_date->format('d') . ' ' . $end_date->format('F');

            $sprints[$i]->stories = SprintStory::leftJoin('stories' , 'sprint_stories.story_id' , 'stories.id')
                ->select('stories.*' , 'sprint_stories.story_rank')
                ->where('sprint_stories.sprint_id' , $sprints[$i]->id)
                ->orderBy('sprint_stories.story_rank', 'asc')
                ->get();

            $sprints[$i]->count_stories = count($sprints[$i]->stories);

            $allPoints = 0; $ToDoPoints = 0; $ProgressPoints = 0; $DonePoints = 0;

            for ($j=0; $j < count($sprints[$i]->stories) ; $j++) {

                if($sprints[$i]->stories[$j]->status == 'to do'){

                        $ToDoPoints += $sprints[$i]->stories[$j]->points;

                } else if($sprints[$i]->stories[$j]->status == 'in progress'){

                        $ProgressPoints += $sprints[$i]->stories[$j]->points;

                } else if($sprints[$i]->stories[$j]->status == 'done'){

                        $DonePoints += $sprints[$i]->stories[$j]->points;
                }
                $allPoints += $sprints[$i]->stories[$j]->points;

                $sprints[$i]->all_points = $allPoints;
                $sprints[$i]->todo_points = $ToDoPoints;
                $sprints[$i]->progress_points = $ProgressPoints;
                $sprints[$i]->done_Points = $DonePoints;
            }
        }

        $allStories = Stories::where([
            ['status' , 'to do'] ,
            ['project_id' , $request->projectID]
        ])->get();

        $backlog = new Collection();

        for ($i=0; $i < count($allStories) ; $i++) {
            $result = SprintStory::where('story_id' , $allStories[$i]->id)->exists();
            if( !$result ){
                $backlog->push($allStories[$i]);
            }
        }

        /* Daily Scrum Meeting */
        $dailyScrum = Meetings::where([
                                ['project_id' , $request->projectID] ,
                                ['type' , 'daily scrum']
                            ])->get();

        /* Response */
        return response()->json([
            'sprints' => [
                'data' => $sprints ,
                'count_sprints' => count($sprints)
            ] ,
            'backlog' => [
                'data' => $backlog ,
                'total_stories' => count($backlog)
            ] ,
            'is_found_daily_scrum' => count($dailyScrum) ,
            'daily_scrum' => $dailyScrum ,
        ]);

    }

    /* Check Store Sprint To Sprint Planning Meeting */
    public function checkCreateSprint(Request $request){

        $sprints  = Sprints::where('project_id' , $request->projectID)->get();
        $meetings = Meetings::where([
            ['project_id' , $request->projectID] ,
            ['type' , 'planning meeting']
        ])->get();

        if(count($meetings) - count($sprints) == 1){

            return response()->json([
                'create_sprint' => true
            ]);

        } else{

            return response()->json([
                'create_sprint' => false
            ]);
        }
    }

    /* Store Sprint For Project */
    public function store(StoreSprintRequest $request)
    {

        // No Sprints In Project ==> store sprint
        $lengthSprintsInProject = Sprints::where('project_id' , $request->project_id)->get();
        if( count($lengthSprintsInProject) == 0 ){

            $startDate = Carbon::parse($request->strat_date);
            $endDate   = Carbon::parse($request->end_date);
            $date      = $endDate->diff($startDate);
            $diffDay   = $date->format('%d');
            $diffMonth = $date->format('%m');

            if($diffDay >= 7 && $diffDay <= 28 && $diffMonth == 0){

                Sprints::create($request->validated());
                return response()->json([
                    'status' => true ,
                    'store_status' => true ,
                    'msg' => 'Successfully Created New Sprint' ,
                ]);

            } else{

                return response()->json([
                    'status' => false ,
                    'store_status' => true ,
                    'msg' => 'Error, this date not match to role' ,
                ]);

            }
        }

        // Found Sprint In Project Status [Todo or In Progress] ==> no store sprint
        $foundSprintTodoOrInProgress = $sprints = Sprints::where([
            ['project_id' , $request->project_id] ,
            ['status' , '<>' ,'done']
        ])->exists();
        if($foundSprintTodoOrInProgress){

            return response()->json([
                'status' => false ,
                'store_status' => false ,
                'msg' => 'Can\'t create sprint, There is a sprint in the works' ,
            ]);

        } else{

            $startDate = Carbon::parse($request->strat_date);
            $endDate   = Carbon::parse($request->end_date);
            $date      = $endDate->diff($startDate);
            $diffDay   = $date->format('%d');
            $diffMonth = $date->format('%m');

            if($diffDay >= 7 && $diffDay <= 28 && $diffMonth == 0){

                Sprints::create($request->validated());
                return response()->json([
                    'status' => true ,
                    'store_status' => true ,
                    'msg' => 'Successfully Created New Sprint' ,
                ]);

            } else{

                return response()->json([
                    'status' => false ,
                    'store_status' => true ,
                    'msg' => 'Error, this date not match to role' ,
                ]);

            }
        }

    }

    /* Add Story To Sprint For Project */
    public function addStoryToSprint(Request $request){

        $foundSprintTodo = Sprints::where([
            ['project_id' , $request->project_id] ,
            ['status' , 'to do']
        ])->exists();

        if($foundSprintTodo){

            $sprintID = Sprints::where([
                ['project_id' , $request->project_id] ,
                ['status' , 'to do']
            ])->get();

            $sprintID = $sprintID[0]->id;

            DB::table('sprint_stories')->insert([
                'sprint_id' => $sprintID,
                'story_id' => $request->story_id
            ]);

            return response()->json([
                'status' => true ,
                'msg' => 'Successfully Add Story To Sprint'
            ]);


        } else{

            return response()->json([
                'status' => false ,
                'msg' => 'There is no sprint to add stories'
            ]);
        }
    }

    /* Remove Story From Sprint For Project */
    public function removeStoryFromSprint(Request $request){

        DB::table('sprint_stories')->where([
            ['story_id' , $request->story_id] ,
            ['sprint_id' , $request->sprint_id]
        ])->delete();

        return response()->json([
            'status' => true ,
            'msg' => 'Successfully Remove Story From Sprint'
        ]);
    }

    /* Update Story Rank */
    public function updateStoryRank(Request $request){

        DB::table('sprint_stories')
            ->where([
                ['story_id' , $request->story_id] ,
                ['sprint_id' , $request->sprint_id] ,
            ])
            ->update(['story_rank' => $request->rank]);

        return response()->json([
            'status' => true
        ]);
    }

    /* Destroy Sprint */
    public function destroy(Sprints $sprint){
        $sprint->delete();

        return response()->json([
            "status"=> true ,
            "msg" => "Successfully Delete Sprint."
        ]);
    }

    /* Start Sprint */
    public function startSprint(Request $request){

        $storiesInSprintID = SprintStory::where('sprint_id' , $request->sprintID)->get();

        $sumStoriesPoint = 0;
        $storiesInfoIsNotEmpty = true;
        for ($i=0; $i < count($storiesInSprintID) ; $i++) {

            $story = Stories::where('id' , $storiesInSprintID[$i]->story_id)->get();

            if($story[0]->title == '' || $story[0]->description == '' || $story[0]->points == '' ){

                $storiesInfoIsNotEmpty = false;

            } else{

                $sumStoriesPoint += $story[0]->points;
            }
        }

        $sprintInfo = Sprints::where('id' , $request->sprintID)->get();

        $startDate = Carbon::parse($sprintInfo[0]->strat_date);
        $endDate   = Carbon::parse($sprintInfo[0]->end_date);
        $date      = $endDate->diff($startDate);
        $diffDay   = $date->format('%d');
        $diffMonth = $date->format('%m');

        if($storiesInfoIsNotEmpty && $sumStoriesPoint != 0){

            if($diffDay >= $sumStoriesPoint && $diffMonth == 0){

                DB::table('sprints')
                ->where('id' , $request->sprintID)
                ->update(['status' => 'in progress']);

                return response()->json([
                    'status' => true ,
                    'msg' => 'Successfully start sprint'
                ]);

            } else{
                return response()->json([
                    'status' => false ,
                    'msg' => 'Faild start sprint, Sprint duration not match with stories duration'
                ]);
            }

        }elseif($storiesInfoIsNotEmpty && $sumStoriesPoint == 0){

            return response()->json([
                'status' => false ,
                'msg' => 'Faild start sprint, Dont have any story to work'
            ]);

        }else{

            return response()->json([
                'status' => false ,
                'msg' => 'Faild start sprint, Stories info not complete'
            ]);
        }
    }

}
