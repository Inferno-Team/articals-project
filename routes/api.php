<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CommonController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\MasterController;
use App\Http\Controllers\UserController;
use App\Models\Artical;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use PharIo\Manifest\Author;

Route::post('/login', [UserController::class, 'login']);
Route::post('/signup', [UserController::class, 'signUp']);
Route::post('/reset', [UserController::class, 'reset']);
Route::get('/get_fields', [CommonController::class, 'getFields']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Admin Routes
Route::group(['middleware' => ['auth:sanctum']], function ($route) {
    // admin [ post ] routes
    $route->post('/admin/approve_user', [AdminController::class, 'approveUser']);
    $route->post('/admin/ban_artical', [AdminController::class, 'banArtical']);
    $route->post('/admin/add_artical', [AdminController::class, 'addArtical']);
    $route->post('/admin/ban_user', [AdminController::class, 'banUser']);
    // admin [ get ] routes
    $route->get('/admin/get_all_users_requests', [AdminController::class, 'getUserRequests']);
    $route->get('/admin/get_banned_users', [AdminController::class, 'getBannedUser']);
});

// Doctor Routes
Route::group(['middleware' => ['auth:sanctum']], function ($route) {
    // doctor [ post ] routes
    $route->post('/doctor/add_artical', [DoctorController::class, 'addArtical']);
    $route->post('/doctor/remove_artical', [DoctorController::class, 'removeArtical']);
    $route->post('/doctor/approve_artical', [DoctorController::class, 'approveArtical']);
    $route->post('/doctor/remove_approved_artical', [DoctorController::class, 'removeApproveArtical']);
    // doctor [ get ] routes
    $route->get('/doctor/get_all_master_requests', [DoctorController::class, 'getMasterRequests']);
    $route->get('/doctor/get_my_articles', [DoctorController::class, 'myArticles']);
});

// Master Routes
Route::group(['middleware' => ['auth:sanctum']], function ($route) {
    // doctor [ post ] routes
    $route->post('/master/add_artical', [MasterController::class, 'addArtical']);
    $route->post('/master/remove_artical', [MasterController::class, 'removeArtical']);
    $route->get('/master/get_my_articles', [MasterController::class, 'myArticles']);
    $route->get('/master/get_doctor_of_field', [MasterController::class, 'getDoctorsOfField']);
});

// normal student routes [ common routes ]
Route::group(['middleware' => ['auth:sanctum']], function ($route) {
    // doctor [ post ] routes
    $route->post('/common/comment', [CommonController::class, 'commentOnArticle']);
    $route->post('/common/make_report', [CommonController::class, 'makeReport']);
    $route->post('/common/search', [CommonController::class, 'search']);
    $route->get('/common/get_field_artical/{field}', [CommonController::class, 'getArticals']);
    $route->get('/common/get_recent_articales', [CommonController::class, 'recentArticles']);
    $route->get('/common/download_article_file/{id}', [CommonController::class, 'downloadFile']);
    $route->get('/common/get_article_details/{id}', [CommonController::class, 'getArticleDetails']);
});
