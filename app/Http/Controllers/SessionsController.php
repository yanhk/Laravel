<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{
    //指定一些只允许未登录用户访问的动作
    public function __construct()
    {
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }


    //用户登录界面
    public function create()
    {
        return view('sessions.create');
    }

    //用户登录提交
    public function store(Request $request)
    {
        $credentials = $this->validate($request, [
            'email' => 'required|email|max:255',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials, $request->has('remember'))) {
            session()->flash('success', '欢迎回来！');

            $fallback = route('users.show', Auth::user());
            return redirect()->intended($fallback);
//            intended  方法可将页面重定向到上一次请求尝试访问的页面上
//            return redirect()->route('users.show', [Auth::user()]);
        } else {
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            return redirect()->back()->withInput();
        }
    }

    //用户退出
    public function destroy()
    {
        Auth::logout();
        session()->flash('success', '您已成功退出！');
        return redirect('login');
    }
}
