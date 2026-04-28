<?php

namespace App\Http\Controllers;

use App\Services\FollowService;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    protected $followService;

    public function __construct(FollowService $followService)
    {
        $this->followService = $followService;
    }

    public function toggle(Request $request, $id)
    {
        return response()->json($this->followService->toggleFollow($request->user(), (int) $id));
    }

    public function followers($id)
    {
        return response()->json($this->followService->getFollowers((int) $id));
    }

    public function following($id)
    {
        return response()->json($this->followService->getFollowing((int) $id));
    }

    public function isFollowing(Request $request, $id)
    {
        return response()->json($this->followService->isFollowing($request->user(), (int) $id));
    }
}
