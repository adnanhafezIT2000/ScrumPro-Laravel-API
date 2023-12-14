<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DefinitionsOfDone;
use App\Http\Requests\StoreDefinitionRequest;
use Illuminate\Support\Facades\DB;

class DefinitionController extends Controller
{

    /********************************************
     * Get All Definitions Of Done For Project
    ********************************************/
    public function getDefinitionForProject(Request $request){

        return DefinitionsOfDone::where('project_id' , $request->projectID)->get() ;
    }

    /**********************************
     * Store New Definitions Of Done
    **********************************/
    public function store(StoreDefinitionRequest $request){

        DefinitionsOfDone::create($request->validated());

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Created New Definition' ,
        ]);
    }

    /****************************
     * Show Definitions Of Done
    ****************************/
    public function showDefinition(Request $request){

        return DefinitionsOfDone::find($request->id);
    }

    /******************************
     * Update Definitions Of Done
    ******************************/
    public function updateDefinition(Request $request , string $id){

        DB::table('definitions_of_dones')
            ->where('id', $id)
            ->update(['description' => $request->description]);

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Updated Definition'
        ]);
    }

    /********************************
     * Destroy Definitions Of Done
    ********************************/
    public function destroyDefinition(Request $request){

        DB::table('definitions_of_dones')->where('id', $request->id)->delete();

        return response()->json([
            'status' => true ,
            'msg' => 'Successfully Deleted Definition' ,
        ]);
    }
}
