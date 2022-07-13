<?php

namespace App\Http\Controllers;

use App\Models\Artical;
use App\Models\BannedArtical;
use App\Models\Field;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MasterController extends Controller
{
    public function addArtical(Request $request)
    {

        // request [ name  , type{artical,research} , university_name  , file(PDF) , doctor_id ]
        $user = Auth::user();
        if ($user->type === 'master') {
            $doctor = User::where('id', $request->doctor_id)->first();

            if (!isset($doctor)) {
                return response()->json([
                    'code' => 300,
                    'message' => "the provided doctor ID is not vaild ID.",
                ], 200);
            }
            if ($doctor->type != 'doctor')
                return response()->json([
                    'code' => 300,
                    'message' => "the provided ID is not a doctor ID.",
                ], 200);
            if ($doctor->field_id != $user->field_id) {
                return response()->json([
                    'code' => 301,
                    'message' => "you can't choose doctor outside your field.",
                ], 200);
            }

            $artical = Artical::create([
                'name' => $request->name,
                'field_id' => $user->field_id,
                'type' => $request->type,
                'university_name' => $request->university_name,
                'file_url' => '',
                'writer_id' => $user->id,
                'doctor_id' => $request->doctor_id
            ]);

            if ($request->hasFile('pdf')) {
                $pdf = $request->file('pdf');
                $file_ext = $pdf->getClientOriginalExtension();
                $file_name = time() . '.' . $file_ext;
                $path = '/public/pdf';
                $pdf->storeAs($path, $file_name);
                $artical->file_url = '/storage/pdf/' . $file_name;
                $artical->save();
            }
            return response()->json([
                'code' => 200,
                'message' => 'artical added and waiting to be approved by doctor [ '
                    . $doctor->first_name . $doctor->last_name  . ' ].',
                'data' => $artical
            ], 200);
        } else {
            return response()->json([
                'code' => 403,
                'message' => "you don't have access to this route."
            ], 200);
        }
    }
    public function removeArtical(Request $request)
    {

        //request [ artical_id ]
        $user = Auth::user();
        if ($user->type === 'master') {
            $artical = Artical::where('id', $request->artical_id)->first();
            if (!isset($artical)) {
                return response()->json([
                    'code' => 300,
                    'message' => 'Artical not foud.'
                ], 200);
            }
            if ($artical->wrtier_id != $user->id) {
                return response()->json([
                    'code' => 301,
                    'message' => "This artical doesn't belongs To you."
                ], 200);
            }
            // check if this artical has been banned or approved
            $bannedArtical = BannedArtical::Where('artical_id', $artical->id)->first();
            $approvedArtical = Artical::where('artical_id', $artical->id)->first();
            if (isset($bannedArtical)) {
                // you can't remove banned artical
                return response()->json([
                    'code' => 302,
                    'message' => "This artical has been banned from admin you can see the cause for more details.",
                    'cause' => $bannedArtical->cause
                ], 200);
            }
            // remove approved artical
            if (isset($approvedArtical)) {
                $approvedArtical->delete();
            }
            $artical->delete();
            return response()->json([
                'code' => 200,
                'message' => "This artical has beeen removed."
            ], 200);
        } else {
            return response()->json([
                'code' => 403,
                'message' => "you don't have access to this route."
            ], 200);
        }
    }
    public function myArticles(Request $request)
    {
        $user = Auth::user();
        if ($user->type === 'master') {
            if ($user->approved != 'yes') {
                return response()->json([
                    'code' => 300,
                    'msg' => "your account not approved yet please wait until been approved.",
                    'articles' => null
                ], 200);
            }
            $articles = Artical::where('writer_id', $user->id)
                ->with(
                    'writer',
                    'doctor',
                    'field',
                    'approved',
                    'bannd',
                    'comments.user'
                )->get()->map->format();
            return response()->json([
                'code' => 200,
                'msg' => "good",
                'articles' => $articles
            ], 200);
        } else {
            return response()->json([
                'code' => 403,
                'msg' => "you don't have access to this route."
            ], 200);
        }
    }
    public function getDoctorsOfField()
    {
        $user = Auth::user();
        if ($user->type === 'master') {
            $doctors = User::where('type', 'doctor')
                ->where('field_id', $user->field_id)->get();
            return response()->json($doctors);
        } else {
            return response()->json([
                'code' => 403,
                'message' => "you don't have access to this route."
            ], 200);
        }
    }
}
