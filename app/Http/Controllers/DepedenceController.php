<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dependence;

class DepedenceController extends Controller
{
    /**********************
     * Store Dependences
    **********************/
    public function store(Request $request){

        $taskID = $request->task_id;
        $typeDependence = $request->type_of_dependence;
        $tasks = $request->tasks_in_dependence;

        /* Type Dependence Is Blocking By */
        if($typeDependence == 'blocking_by'){

            // For Loop To Check Dependences
            $blockingByCheck = 0;
            for ($i=0; $i <count($tasks) ; $i++) {

                $check = Dependence::where([
                    'task_id' => $tasks[$i] ,
                    'blocking_by' => $taskID ,
                ])->exists();
                if($check){
                    $blockingByCheck ++;
                }
                if($taskID == $tasks[$i]){
                    $blockingByCheck ++;
                }
            }

            // Insert If No Error In Dependences
            if($blockingByCheck == 0){

                for ($i=0; $i <count($tasks) ; $i++) {
                    Dependence::create([
                        'task_id' => $taskID,
                        'blocking_by' => $tasks[$i],
                    ]);
                }
                return response()->json([
                    "status" => true,
                    "msg"    => "Successfully Create Dependence",
                ]);

            } else{

                return response()->json([
                    "status" => false,
                    "msg"    => "Faild, Dependence Is Incorrect",
                ]);
            }

        }

        /* Type Dependence Is Blocked */
        elseif($typeDependence == 'blocked'){

            // For Loop To Check Dependences
            $blockedCheck = 0;
            for ($i=0; $i <count($tasks) ; $i++) {

                $check = Dependence::where([
                    'task_id' => $taskID,
                    'blocking_by' => $tasks[$i],
                ])->exists();
                if($check){
                    $blockedCheck ++;
                }
                if($taskID == $tasks[$i]){
                    $blockedCheck ++;
                }
            }

            // Insert If No Error In Dependences
            if($blockedCheck == 0){

                for ($i=0; $i <count($tasks) ; $i++) {

                    Dependence::create([
                        'task_id' => $tasks[$i],
                        'blocking_by' => $taskID ,
                    ]);
                }
                return response()->json([
                    "status" => true,
                    "msg"    => "Successfully Create Dependence",
                ]);

            } else{

                return response()->json([
                    "status" => false,
                    "msg"    => "Faild, Dependence Is Incorrect",
                ]);
            }

        }
    }
}
