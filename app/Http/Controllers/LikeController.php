<?php

namespace App\Http\Controllers;

use App\Services\LikeService;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    protected $likeService;

    public function __construct(LikeService $likeService)
    {
        $this->likeService = $likeService;
    }

    public function toggle(Request $request, $id)
    {
        return response()->json($this->likeService->toggle($request->user(), (int) $id));
    }

    public function store(Request $request, $id)
    {
        return response()->json($this->likeService->like($request->user(), (int) $id));
    }

    public function destroy(Request $request, $id)
    {
        return response()->json($this->likeService->unlike($request->user(), (int) $id));
    }

    public function index($id)
    {
        return response()->json($this->likeService->getLikes((int) $id));
    }
}
