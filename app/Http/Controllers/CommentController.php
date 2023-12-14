<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Comments;
use App\Http\Requests\StoreCommentRequest;
use Illuminate\Support\Facades\DB;


class CommentController extends Controller
{

    public function getCommentsForStory(Request $request){

        $comments = DB::table('comments')
        ->leftJoin('users', 'comments.user_id', '=', 'users.id')
        ->select('comments.*', 'users.full_name' , 'users.avatar' , 'users.gender')
        ->where('story_id',$request->story)
        ->get();

        return $comments;
    }

    public function store(StoreCommentRequest $request){
        Comments::create($request->validated());

        return response()->json([

            'status' => true ,
            'msg' => 'Successfully Created New Comment' ,
        ]);
    }
}
