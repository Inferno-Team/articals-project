<?php

namespace App\Http\Controllers;

use App\Models\ApprovedArtical;
use App\Models\Artical;
use App\Models\Field;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DoctorController extends Controller
{
    public function addArtical(Request $request)
    {

        // request [ name  , type{artical,research} , university_name  , file(PDF) ]
        $user = Auth::user();
        if ($user->type === 'doctor') {
            $artical = Artical::create([
                'name' => $request->name,
                'field_id' => $user->field_id,
                'type' => $request->type,
                'university_name' => $request->university_name,
                'file_url' => '',
                'writer_id' => $user->id,
            ]);
            if ($request->hasFile('pdf')) {
                $pdf = $request->file('pdf');
                $file_ext = $pdf->getClientOriginalExtension();
                $file_name = time() . '.' . $file_ext; // 4654654654654.pdf
                $path = '/public/pdf';
                $pdf->storeAs($path, $file_name); // /pdf/4654654654654.pdf
                $artical->file_url =  '/storage/pdf/' . $file_name;
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

    public function removeArtical(Request $request)
    {
        // request [ artical_id ]
        $user = Auth::user();
        if ($user->type === 'doctor') {
            $artical = Artical::where('id', $request->artical_id)->first();
            if ($artical->writer_id  === $user->id) {
                $approvedArtical = ApprovedArtical::where('artical_id', $artical->id)->first();
                if (isset($approvedArtical))
                    $approvedArtical->delete();
                $artical->delete();
                return response()->json([
                    'code' => 200,
                    'message' => "artical deleted",
                    'artical' => $artical
                ], 200);
            } else {
                return response()->json([
                    'code' => 301,
                    'message' => "The provided ID of artical is not yours."
                ], 200);
            }
        } else {
            return response()->json([
                'code' => 403,
                'message' => "you don't have access to this route."
            ], 200);
        }
    }
    public function approveArtical(Request $request)
    { // request [ artical_id  ]
        $user = Auth::user();
        if ($user->type == 'doctor') {
            $artical = Artical::where('id', $request->artical_id)->first();
            // check doctor_id 
            if (isset($artical)) {
                if ($artical->doctor_id === $user->id) {
                    $approvedArtical = ApprovedArtical::create([
                        'approver_id' => $user->id,
                        'artical_id' => $artical->id
                    ]);
                    return response()->json([
                        'code' => 200,
                        'message' => "This artical has been approved",
                        'data' => $approvedArtical
                    ], 200);
                } else {
                    return response()->json([
                        'code' => 302,
                        'message' => "You can't approve this artical cause you're not the requested doctor"
                    ], 200);
                }
            } else {
                return response()->json([
                    'code' => 301,
                    'message' => "Artical not found."
                ], 200);
            }
        } else {
            return response()->json([
                'code' => 300,
                'message' => "you dont have access to this route cuz ur not a doctor."
            ], 200);
        }
    }
    public function removeApproveArtical(Request $request)
    { // request [ artical_id  ]
        $user = Auth::user();
        if ($user->type === 'doctor') {
            $artical = Artical::where('id', $request->artical_id)->first();
            // check doctor_id 
            if (isset($artical)) {
                if ($artical->doctor_id === $user->id) {
                    $approvedArtical = ApprovedArtical::where('artical_id', $artical->id)->first();
                    $approvedArtical->delete();
                    $artical->delete();
                    return response()->json([
                        'code' => 200,
                        'message' => "This artical has been removed"
                    ], 200);
                } else {
                    return response()->json([
                        'code' => 302,
                        'message' => "You can't remove this artical cause you're not the requested doctor"
                    ], 200);
                }
            } else {
                return response()->json([
                    'code' => 301,
                    'message' => "Artical not found."
                ], 200);
            }
        } else {
            return response()->json([
                'code' => 403,
                'message' => "you don't have access to this route."
            ], 200);
        }
    }

    public function getMasterRequests()
    {
        $user = Auth::user();
        if ($user->type === 'doctor') {
            $requests = Artical::where('doctor_id', $user->id)->with('writer')->get();
            $approvedRequests = ApprovedArtical::whereHas(
                'artical',
                fn ($artical) => $artical->where('doctor_id', $user->id)
            )->get();

            foreach ($approvedRequests as $artical) {
                $removeableArtical = $requests->where('id', $artical->artical_id)->first();
                if (isset($removeableArtical)) {
                    $requests = $requests->except($artical->artical_id);
                    // info($requests);
                } else {
                    info('artical not found with ID : #' . $artical->id);
                }
            }
            return response()->json($requests, 200);
        } else {
            return response()->json([
                'code' => 403,
                'message' => "you don't have access to this route."
            ], 200);
        }
    }

    public function myArticles()
    {
        $user = Auth::user();
        if ($user->type === 'doctor') {
            $articles = Artical::where('writer_id', $user->id)
                ->with(
                    'writer',
                    'field',
                    'approved',
                    'bannd',
                    'comments.user',
                )->paginate(10);
            return response()->json($articles, 200);
        } else {
            return response()->json([
                'code' => 403,
                'message' => "you don't have access to this route."
            ], 200);
        }
    }
}
