<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\QuestionResource;
use App\Models\Question;

class QuestionController extends Controller
{
    public function index()
    {
        $questions = Question::active()
            ->with('options')
            ->orderBy('order')
            ->get();

        return QuestionResource::collection($questions);
    }
}
