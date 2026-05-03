<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataClassification;

class DataClassificationController extends Controller
{
    public function __invoke()
    {
        return DataClassification::orderBy('id')->get();
    }
}
