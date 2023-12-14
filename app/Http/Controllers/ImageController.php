<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use App\Models\Users;
use Illuminate\Support\Facades\DB;

class ImageController extends Controller
{
    
    public function updateUserImage(Request $request){

        $imageName      = $_FILES['image']['name'];
        $imagePosition  = $_FILES['image']['tmp_name'];
        $imageType      = $_FILES['image']['type'];
        $imageSize      = $_FILES['image']['size'];
        $imageExtention = explode('.' , $imageName);
        $imageExtention = strtolower(end($imageExtention));

        $getUserName = Users::select('full_name')->where('id' , $request->userID)->get();
        $fullName = explode(' ' , $getUserName[0]->full_name);

        $newImageName = rand(0 , 1000000000).$fullName[0].$fullName[1].time().'.'. $imageExtention;

        $allowedExtensions = array('jpg' , 'gif' , 'jpeg' , 'png');

        if(! in_array($imageExtention , $allowedExtensions)){

            return response()->json([
                'status' => false ,
                'msg' => 'Faild Upload Image, Allow File Extentions (jpg , gif , jpeg , png)'
            ]);

        }else{

            DB::table('users')
                ->where('id', $request->userID)
                ->update(['avatar' => $newImageName]);

            move_uploaded_file($imagePosition , "C:/scrum_pro/src/assets/users/" . $newImageName);

            return response()->json([
                'status' => true ,
                'msg' => 'Successfully Upload Image' ,
            ]);
        }

    }
}
