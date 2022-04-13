<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function login(Request $request)
    {
        $valid = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' =>  'required'
        ]);
        if ($valid->fails())
            return response()->json(['code' => 400, 'message' => 'Bad Request'], 200);

        $user = User::where('email', $request->email)->first();
        if (!isset($user)) {
            return response()->json(['code' => 300, 'message' => 'User not found'], 200);
        }
        if (!Hash::check($request->password, $user->password))
            return response()->json(['code' => 301, 'message' => 'Do not match our records!!'], 200);

        if ($user->approved == 'no') {
            return response()->json(['code' => 302, 'message' => 'User approvel denided'], 200);
        }

        if ($user->approved == 'waiting') {
            return response()->json(['code' => 303, 'message' => 'User not approved yet.'], 200);
        }


        $tokenResult = $user->createToken('authToken')->plainTextToken;
        return response()->json([
            'code' => 200,
            'token' => $tokenResult,
            'message' => 'good',
            'type' => $user->type
        ], 200);
    }
    public function signUp(Request $request)
    {
        // request [ firstname, lastname , email , field , type ,  ]
        $valid = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' =>  'required',
            'first_name' =>  'required',
            'last_name' =>  'required',
            'type' =>  'required',
        ]);
        $user = User::where('email', $request->email)->first();
        if (isset($user)) {
            return response()->json(['code' => 400, 'message' => 'this email already in use.'], 200);
        }
        if ($valid->fails())
            return response()->json(['code' => 400, 'message' => 'Bad Request'], 200);
        $user = User::create([
            'email' => $request->email,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'password' => Hash::make($request->password),
            'field_id' => $request->field_id,
            'type' => $request->type,
            'approved' => $request->type == 'normal' ? 'yes' : 'waiting',
            'fcm_token' => $request->fcm_token
        ]);
        // $adminUser = User::where('type', 'admin')->first();
        // FCMService::send($adminUser->fcm_token, [
        //     'title' => 'New User Account',
        //     'body' => 'New User Account has been requested with name : ' . $user->first_name,
        // ], [
        //     'message' => ''
        // ]);
        if (isset($user)) {
            return response()->json([
                'code' => 200,
                'message' => "user created successfully" . ($user->approved != 'yes' ? ' and waiting admin approvel' : ''),
            ], 200);
        } else {
            return response()->json([
                'code' => 300,
                'message' => "user can't be created now."
            ], 200);
        }
    }
    public function reset(Request $request)
    {
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->save();
        return response()->json('good', 200);
    }
}
