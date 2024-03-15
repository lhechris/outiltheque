<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UsersController extends Controller
{
        /**
         * Store a newly created resource in storage.
         *
         * @param \Illuminate\Http\Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        public function update(Request $request): \Illuminate\Http\JsonResponse
        {            

            $user = Auth()->user();

            $validateReq = Validator::make($request->all(),
            [
                'id' => 'required',
                'email' => 'required|email',
                'name' => 'required'
            ]);

            if ($validateReq->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateReq->errors()
                ], 401);
            }

            $data = User::find($request->id);

            if ($user->id != $request->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'Not authorized',
                    'errors' => 'bad id'
                ], 401);
            }

            $data->email = $request->email;
            $data->name = $request->name;

            if (($request->password!="") && ($request->oldpassword!="")) {
                
                if (! Hash::check($request->oldpassword,$user->password)) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Bad password',
                        'errors' => $validateReq->errors()
                    ], 401);                    
                } else {
                    $data->password = Hash::make($request->password);
                }
            }

            $data->update();
            return response()->json(['status' => true, 'data' => $data], 202);


        }
}
