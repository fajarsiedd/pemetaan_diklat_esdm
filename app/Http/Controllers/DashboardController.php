<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index(): Response
    {
        return Inertia::render('Dashboard/Index', [
            'stats' => [
                'total_users' => User::count(),
                'new_users_this_month' => User::where('created_at', '>=', now()->startOfMonth())->count(),
                'latest_user' => User::latest('id')->first()?->name,
            ],
        ]);
    }
}
