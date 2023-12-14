<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Meetings;
use App\Http\Requests\StoreMeetingRequest;
use Illuminate\Support\Facades\DB;

class MeetingController extends Controller
{

    /* Get All Sprint Planning Meeting For Project */
    public function getAllSprintPlanningMeeting(Request $request){

        $planningMeetings = Meetings::where([
            ['project_id' , $request->projectID] ,
            ['type' , 'planning meeting']
        ])->orderBy('created_at' , 'asc')->get();

        return $planningMeetings;
    }

    /* Store Sprint Planning Meeting */
    public function storePlanningMeeting(StoreMeetingRequest $request){

        Meetings::create($request->validated());

        return response()->json([
            'status' => true ,
            'msg' => 'Successfully Created Sprint Planning Meeting' ,
        ]);
    }

    /* Store Daily Scrum Meeting */
    public function storeDailyScrumMeeting(Request $request){

        $meeting = new Meetings;

        $meeting->project_id = $request->project_id;
        $meeting->start_time = $request->start_time;
        $meeting->end_time   = $request->end_time;
        $meeting->type       = $request->type;

        $meeting->save();

        return response()->json([
            'status' => true ,
            'msg' => 'Successfully Created Daily Scrum Meeting' ,
        ]);
    }

    /* Update Daily Scrum meeting */
    public function updateDailyScrumMeeting(Request $request){

        DB::table('meetings')
                    ->where([
                        ['project_id' , $request->project_id] ,
                        ['type' , $request->type]
                    ])
                    ->update([
                        'start_time' => $request->start_time,
                        'end_time'   => $request->end_time
                    ]);

        return response()->json([
            'status' => true ,
            'msg' => 'Successfully Updated Daily Scrum Meeting' ,
        ]);
    }

    /* Delete Meeting */
    public function destroy(Meetings $meeting){
        $meeting->delete();

        return response()->json([
            "status"=> true ,
            "msg" => "Successfully Delete Meeting"
        ]);
    }
}
