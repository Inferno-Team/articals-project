<?php

namespace App\Http\Controllers;

use App\Models\ApprovedArtical;
use App\Models\Artical;
use App\Models\Comment;
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
        return response()->json(Field::all(), 200);
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
        // check user field first 
        // is user is admin or normal we will return all fields recent articles
        // if user type is doctor OR master we will return only 
        // the recent articles acording to there field
        $user  = Auth::user();
        if ($user->type == 'admin' || $user->type == 'normal')
            $articals = ApprovedArtical::orderBy('created_at', 'desc')->paginate(3);
        else {
            $articals = ApprovedArtical::orderBy('created_at', 'desc')
            ->whereHas('artical', function ($query) use ($user) {
                return $query->where('field_id', $user->field_id);
            })->paginate(3);
        }
        $collection =  $articals->getCollection()->map->format();
        $articals->setCollection($collection);

        return response()->json($articals, 200);
    }

    public function getArticleDetails($id)
    {

        $article = ApprovedArtical::with(
            'artical',
            'artical.writer',
            'artical.doctor',
            'artical.field',
            'artical.comments',
        )->where('artical_id', $id)->first();
        //info($article);
        return response()->json($article->artical, 200);
    }
    public function downloadFile($id)
    {
        $artical = Artical::where('id', $id)->first();
        if (isset($artical)) {
            $currentURL = URL::to('/');
            $url = $currentURL . '/storage/' . $artical->file_url;
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
    public function commentOnArticle(Request $request)
    {
        // request [ id , comment ]
        $art = Artical::find($request->id);
        if (!isset($art)) {
            // return empty array
            return response()->json([
                'code' => 401,
                'message' => 'can\'t comment article not found',
                'data' => null,
            ]);
        }
        $comment = Comment::create([
            'comment' => $request->comment,
            'user_id' => Auth::user()->id,
            'article_id' => $art->id,
        ]);
        return response()->json([
            'code' => 200,
            'message' => 'comment',
            'data' => $comment
        ]);
    }
}
