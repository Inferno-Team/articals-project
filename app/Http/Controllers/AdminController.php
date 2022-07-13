<?php

namespace App\Http\Controllers;

use App\Models\ApprovedArtical;
use App\Models\Artical;
use App\Models\BannedArtical;
use App\Models\BannedUser;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    public function approveUser(Request $request)
    {
        // request [ user_id , approved{yes,no} ]
        $user = Auth::user();
        if ($user->type === 'admin') {
            $approveUser = User::where('id', $request->user_id)->first();
            if (isset($approveUser)) {
                $approveUser->approved = $request->approved;
                $approveUser->save();
                return response()->json([
                    'code' => 200,
                    'message' => "User "
                        . $approveUser->email .
                        " status changed to [ " .
                        $request->approved . " ]."
                ], 200);
            } else {
                return response()->json([
                    'code' => 301,
                    'message' => "There is no user with provided id."
                ], 200);
            }
        } else {
            return response()->json([
                'code' => 403,
                'message' => "you don't have access to this route."
            ], 200);
        }
    }

    public function banArtical(Request $request)
    {
        // request [ artical_id , cause ]
        $user = Auth::user();
        if ($user->type === 'admin') {
            $artical = Artical::where('id', $request->artical_id)->first();
            if (isset($artical)) {
                BannedArtical::create([
                    'ban_id' => $user->id,
                    'artical_id' => $artical->id,
                    'cause' => $request->cause,
                ]);
                return response()->json([
                    'code' => 200,
                    'message' => "Artical : " . $artical->name . " has been banned."
                ], 200);
            }
        } else {
            return response()->json([
                'code' => 403,
                'message' => "you don't have access to this route."
            ], 200);
        }
    }
    public function addArtical(Request $request)
    {

        // request [ name , field_id , type{artical,research} , university_name , writer_id , file(PDF) ]
        $user = Auth::user();
        if ($user->type === 'admin') {
            $artical = Artical::create([
                'name' => $request->name,
                'field_id' => $request->field_id,
                'type' => $request->type,
                'university_name' => $request->university_name,
                'file' => '',
            ]);
            if ($request->hasFile('pdf')) {
                $pdf = $request->pdf;
                $file_ext = $pdf->getClientOriginalExtension();
                $file_name = time() . '.' . $file_ext;
                $path = 'images/';
                $pdf->move($path, $file_name);
                $artical->file = $path . '/' . $file_name;
                $artical->save();
            }
            ApprovedArtical::create([
                'approver_id' => $user->id,
                'artical_id' => $artical->id
            ]);
            return response()->json([
                'code' => 200,
                'message' => 'artical added and approved.',
                'artical' => $artical
            ], 200);
        } else {
            return response()->json([
                'code' => 403,
                'message' => "you don't have access to this route."
            ], 200);
        }
    }
    public function banUser(Request $request)
    {
        // request [ user_id , cause ]
        $user = Auth::user();
        if ($user->type === 'admin') {
            // check if the provided user id is vaild
            $_user = User::where('id', $request->id)->first();
            if (isset($_user)) {
                $bannedUser = BannedUser::create([
                    'user_id' => $_user->id,
                    'cause' => $request->cause
                ]);
                return response()->json([
                    'code' => 200,
                    'message' => "User : [" . $_user->name . " ] has been banned .",
                    'cause' => $request->cause
                ], 200);
            } else {
                return response()->json([
                    'code' => 301,
                    'message' => "The provided user ID is not vaild."
                ], 200);
            }
        } else {
            return response()->json([
                'code' => 403,
                'message' => "you don't have access to this route."
            ], 200);
        }
    }
    public function getUserRequests()
    {
        $user = Auth::user();
        if ($user->type === 'admin') {
            return response()->json(User::where('type', 'doctor')
            ->where('type', 'master')
            ->orWhere('approved', 'waiting')
            ->with('field')
            ->get()->map->format(), 200);
        } else {
            return response()->json([
                'code' => 403,
                'message' => "you don't have access to this route."
            ], 200);
        }
    }
    public function getBannedUser()
    {
        $user = Auth::user();
        if ($user->type === 'admin') {
            return response()->json(
                BannedUser::with('user')->get(),
                200
            );
        } else {
            return response()->json([
                'code' => 403,
                'message' => "you don't have access to this route."
            ], 200);
        }
    }
}
