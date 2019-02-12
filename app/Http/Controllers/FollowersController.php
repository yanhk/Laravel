<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FollowersController extends Controller
{
    //限制登录用户才可访问
    public function __construct()
    {
        $this->middleware('auth');
    }

    //关注
    public function store(User $user)
    {
        $this->authorize('follow', $user);

        if(!Auth::user()->isFollowing($user->id)){
            Auth::user()->follow($user->id);
        }

        return redirect()->route('users.show', $user->id);
    }

    //删除关注
    public function destory(User $user)
    {
        $this->authorize('follow', $user);
//dd(Auth::user()->isFollowing($user->id));
        if(Auth::user()->isFollowing($user->id)){
            Auth::user()->unfollow($user->id);
        }

        return redirect()->route('users.show', $user->id);
    }

}
