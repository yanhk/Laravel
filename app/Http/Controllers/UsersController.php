<?php

namespace App\Http\Controllers;

use Mail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UsersController extends Controller
{
    public function __construct()
    {
//        except 方法来设定 指定动作 不使用 Auth 中间件进行过滤
//        开启未登录用户访问权限。
        $this->middleware('auth', [
            'except' => ['create', 'store', 'show', 'index', 'confirmEmail']
        ]);
        $this->middleware('guest', [
            'only' => ['create']
        ]);
    }


    //用户注册表单
    public function create()
    {
        return view('users.create');
    }

    //用户注册表单提交
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');

//        Auth::login($user);
//        session()->flash('success', '欢迎，您将在这里开启一段新的旅程~');
//
//        return redirect()->route('users.show', [$user]);
    }

    //用户信息展示
    public function show(User $user)
    {
        return view('users.show', compact('user'));
    }

    //用户编辑页面
    public function edit(User $user)
    {
        $this->authorize('update', $user);
        return view('users.edit', compact('user'));
    }

    //用户编辑更新
    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);

        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'required|confirmed|min:6'
        ]);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user);
    }

    //用户列表
    public function index()
    {
//        $users = User::all();
        $users = User::paginate(10);
        return view('users.index', compact('users'));
    }

    //用户删除
    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    //发送邮件
    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($to, $subject) {
            $message->to($to)->subject($subject);
        });
    }
//    protected function sendEmailConfirmationTo($user)
//    {
//        $view = 'emails.confirm';
//        $data = compact('user');
//        $from = 'summer@example.com';
//        $name = 'Summer';
//        $to = $user->email;
//        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";
//
//        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
//            $message->from($from, $name)->to($to)->subject($subject);
//        });
//    }


    //激活用户
    public function confirmEmail($token)
    {
        $user = User::where('activation_token', $token)->firstOrFail();

        $user->activated = true;
        $user->activation_token = null;
        $user->save();

        Auth::login($user);
        session()->flash('success', '恭喜你，激活成功！');
        return redirect()->route('users.show', [$user]);
    }
}
