<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\PostController;

// 1. ROTAS PÚBLICAS (Não precisam de login)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// 2. ROTAS PROTEGIDAS (Precisam do token Sanctum)
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/refresh', [AuthController::class, 'refresh']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // Users
    Route::get('/users/search', [UserController::class, 'search']);
    Route::get('/users/suggestions', [UserController::class, 'suggestions']);
    Route::get('/users/{username}', [UserController::class, 'show']);
    Route::put('/users/me', [UserController::class, 'update']);
    Route::post('/users/me/avatar', [UserController::class, 'updateAvatar']);

    // Follows
    Route::post('/users/{id}/follow', [FollowController::class, 'toggle']);
    Route::delete('/users/{id}/follow', [FollowController::class, 'toggle']);
    Route::get('/users/{id}/followers', [FollowController::class, 'followers']);
    Route::get('/users/{id}/following', [FollowController::class, 'following']);
    Route::get('/users/{id}/is-following', [FollowController::class, 'isFollowing']);

    // Posts
    Route::post('/posts', [PostController::class, 'store']);
    Route::get('/posts/{id}', [PostController::class, 'show']);
    Route::put('/posts/{id}', [PostController::class, 'update']);
    Route::delete('/posts/{id}', [PostController::class, 'destroy']);
    Route::get('/users/{id}/posts', [PostController::class, 'userPosts']);

    // Feed
    Route::get('/feed', [App\Http\Controllers\FeedController::class, 'index']);

    // Curtidas
    Route::post('/posts/{id}/like', [App\Http\Controllers\LikeController::class, 'toggle']);
    Route::delete('/posts/{id}/like', [App\Http\Controllers\LikeController::class, 'toggle']);
    Route::get('/posts/{id}/likes', [App\Http\Controllers\LikeController::class, 'index']);

    // Comentários
    Route::post('/posts/{id}/comments', [App\Http\Controllers\CommentController::class, 'store']);
    Route::get('/posts/{id}/comments', [App\Http\Controllers\CommentController::class, 'index']);
    Route::put('/comments/{id}', [App\Http\Controllers\CommentController::class, 'update']);
    Route::delete('/comments/{id}', [App\Http\Controllers\CommentController::class, 'destroy']);
});
