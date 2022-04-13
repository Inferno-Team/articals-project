<?php

namespace App\Http\Controllers;

use App\Models\ApprovedArtical;
use App\Models\Artical;
use App\Models\Field;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class CommonController extends Controller
{
    public function makeReport(Request $request)
    {
        //request [ problem , artical_id ]
        $user = Auth::user();
        if ($user->type != 'admin') {
            // check artical 
            $artical = Artical::where('id', $request->artical_id)->first();
            if (!isset($artical)) {
                return response()->json([
                    'code' => 300,
                    'message' => "Artical not found."
                ], 200);
            }

            $report = Report::create([
                'user_id' => $user->id,
                'artical_id' => $request->artical_id,
                'problem' => $request->problem
            ]);
            return response()->json([
                'code' => 200,
                'message' => "report has been make about this artical.",
                'report' => $report
            ], 200);
        } else {
            return response()->json([
                'code' => 403,
                'message' => "as an admin you can't make report about artical you can ban it immediately :)"
            ], 200);
        }
    }
    public function search(Request $request)
    {

        // request [ artical_name  ]
        $name = $request->artical_name;
        $articals = ApprovedArtical::whereHas(
            'artical',
            fn ($q) => $q->where('name', 'like', '%' . $name . '%')
        )->with('artical')->get();
        return response()->json($articals, 200);
    }
    public function getFields()
    {
        return response()->json(Field::get(), 200);
    }
    public function getArticals($field)
    {
        $articals = ApprovedArtical::whereHas(
            'artical',
            fn ($query) => $query->where('field_id', $field)
        )->with('artical', 'artical.writer', 'artical.doctor')->paginate(15);
        return response()->json($articals, 200);
    }
    public function recentArticles()
    {
        $articals = ApprovedArtical::orderBy('created_at')->with(
            'artical',
            'artical.writer',
            'artical.doctor',
            'artical.field',

        )->paginate(15);
        return response()->json($articals, 200);
    }
    public function downloadFile($id)
    {
        $artical = Artical::where('id', $id)->first();
        if (isset($artical)) {
            $currentURL = URL::to('/');
            $url = $currentURL.'/storage/'.$artical->file_url;
            $artical->download_number = $artical->download_number + 1;
            $artical->save();
            return response()->json([
                'url' => $url
            ], 200);
        } else {
            return response()->json([
                'url' => null
            ], 200);
        }
    }
}
