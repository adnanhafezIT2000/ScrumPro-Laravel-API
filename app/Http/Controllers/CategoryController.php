<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categories;
use App\Models\Users;
use App\Http\Requests\StoreCategoryRequest;

class CategoryController extends Controller
{

    /**********************
     * Get All Categories
    **********************/
    public function index(){

        $categories = Categories::all();

        for ($i=0; $i < count($categories) ; $i++) {

            $getUsersCount = Users::where('category_id' , $categories[$i]->id)->get();

            $categories[$i]->users_count = count($getUsersCount);
        }

        return $categories;
    }

    /************************
     * Store New categories
    ************************/
    public function store(StoreCategoryRequest $request){

        Categories::create($request->validated());

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Created New Category' ,
        ]);
    }

    /**********************
     * Show Category Info
    **********************/
    public function show(Categories $category){

        return $category;
    }

    /*******************
     * Update Category
    *******************/
    public function update(StoreCategoryRequest $request , Categories $category){

        $category->update($request->validated());

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Updated Category'
        ]);
    }

    /********************
     * Destroy Category
    ********************/
    public function destroy(Categories $category){

        $category->delete();

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Deleted Category'
        ]);
    }
}
