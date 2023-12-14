<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcceptanceCritiria;
use App\Http\Requests\StoreAcceptanceRequest;
use Illuminate\Support\Facades\DB;

class AcceptanceCritiriaController extends Controller
{

    /********************************************
     * Get All Acceptances Critiria For Story
    ********************************************/
    public function getAcceptanceCritiriaForStory(Request $request){

        return AcceptanceCritiria::where('story_id' , $request->id)
                ->orderByDesc('created_at')->get();
    }

    /*****************************************
     * Store Acceptance Critiria For Story
    *****************************************/
    public function store(StoreAcceptanceRequest $request){

        AcceptanceCritiria::create($request->validated());

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Created New Acceptance Critiria' ,
        ]);
    }

    /*********************************
     * Destroy Acceptances Critiria
    *********************************/
    public function destroy(string $id){

        DB::table('acceptance_critirias')->where('id', $id)->delete();

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Deleted Acceptance Critiria' ,
        ]);
    }
}
