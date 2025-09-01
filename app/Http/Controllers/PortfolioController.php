<?php

namespace App\Http\Controllers;

use App\Models\User;

class PortfolioController extends Controller
{
    public function show(User $user)
    {
        $achievements = $user->achievements()
            ->where('visibility', 'public')
            ->latest('achieved_at')
            ->paginate(12);

        return view('portfolio.show', compact('user', 'achievements'));
    }
}

