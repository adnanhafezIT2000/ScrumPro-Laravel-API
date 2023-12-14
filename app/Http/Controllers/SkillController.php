<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Skills;
use App\Models\SkillUser;
use App\Http\Requests\StoreSkillRequest;
use Illuminate\Support\Facades\DB;

class SkillController extends Controller
{

    /*****************************
     * Show Skills In Category
    *****************************/
    public function show(string $category_id){

        $skills = Skills::where('category_id' , $category_id)->get();

        for ($i=0; $i < count($skills) ; $i++) {

            $getUsersCount = SkillUser::where('skill_id' , $skills[$i]->id)->get();

            $skills[$i]->users_count = count($getUsersCount);
        }
        
        return $skills;
    }

    /*******************
     * Show Skill Info
    *******************/
    public function showSkill(Skills $skill){

        return $skill;
    }

    /********************
     * Store New Skills
    ********************/
    public function store(StoreSkillRequest $request){

        Skills::create($request->validated());

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Created New Skill' ,
        ]);
    }

    /*****************
     * Update Skill
    *****************/
    public function update(StoreSkillRequest $request, Skills $skill){

        $skill->update($request->validated());

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Updated Skill' ,
        ]);
    }

    /*****************
     * Destroy Skill
    *****************/
    public function destroy(Skills $skill){

        $skill->delete();

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Deleted Skill' ,
        ]);
    }
}
