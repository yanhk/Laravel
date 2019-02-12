<?php

namespace App\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Auth;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    //获取用户头像
    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
    }

    //boot 方法会在用户模型类完成初始化之后进行加载，因此我们对事件的监听需要放在该方法中
    public static function boot()
    {
        parent::boot();
        //监听 creating 方法
        static::creating(function ($user) {
            $user->activation_token = str_random(30);
        });
    }

    //指明一个用户拥有多条微博。
    public function statuses()
    {
        return $this->hasMany(Status::class);
    }

    public function feed()
    {
//        return $this->statuses()
//            ->orderBy('created_at', 'desc');
        $user_ids = $this->followings->pluck('id')->toArray();
        array_push($user_ids, $this->id);
        return Status::whereIn('user_id', $user_ids)
            ->with('user')
            ->orderBy('created_at', 'desc');
    }

    //粉丝关系列表
    public function followers()
    {
        return $this->belongsToMany(User::Class, 'followers', 'user_id', 'follower_id');
    }

    //用户关注人列表
    public function followings()
    {
        return $this->belongsToMany(User::Class, 'followers', 'follower_id', 'user_id');
    }

    //关注
    public function follow($user_ids)
    {
        if(!is_array($user_ids)){
            $user_ids = compact('user_ids');
        }
        $this->followers()->sync($user_ids, false);
    }

    //取消关注
    public function unfollow($user_ids)
    {

        if(!is_array($user_ids)){
            $user_ids = compact('user_ids');
        }
        $this->followers()->detach($user_ids);
    }

    //判断当前登录用户是否关注B
    public function isFollowing($user_id)
    {
//        dd($this->followings->contains($user_id));
        return $this->followers->contains($user_id);
    }
}
