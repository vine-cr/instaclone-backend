<?php

namespace App\Http\Controllers;

use App\Services\FeedService;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    protected $feedService;

    public function __construct(FeedService $feedService)
    {
        $this->feedService = $feedService;
    }

    public function index(Request $request)
    {
        return response()->json($this->feedService->getFeed($request->user()));
    }
}
