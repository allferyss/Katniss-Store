<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;

class ActivityLogController extends Controller
{
    public function index()
    {
        return view('admin.activity-logs.index', [
            'logs' => ActivityLog::with('user')->latest()->paginate(30),
        ]);
    }
}
