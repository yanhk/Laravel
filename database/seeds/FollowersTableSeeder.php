<?php

use Illuminate\Database\Seeder;
use App\Models\User;
class FollowersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $users = User::all();
        $user = $users->first();
        $user_id = $user->id;

        //获取去除掉ID 为 1 的所有用户ID
        $followers = $users->slice(1);
        $follower_ids = $followers->pluck('id')->toArray();

        //关注除了1 号用户以外的所有用户
        $user->follow($follower_ids);

        //除1以外的用户关注1
        foreach ($followers as $follower){
            $follower->follow($user_id);
        }
    }
}
