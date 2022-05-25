<?php

namespace App\System\Extensions\EGame\Application;

use App\Http\Traits\App;

abstract class GameApp {

    use App;

    /*
     * If true, user can import an avatar
     */
    protected $user_avatar_import = true;

    /**
     * @param $u = user_id
     * Determine user avatar
     * @return string
     * @throws \Exception
     */
    public function userAvatar($u)
    {
        if($u == "this" && $this->auth()->isLogin()) {
            $user = $this->auth();
            if($user->getAvatarBrut() != null)
                return action("Controller@storage_public_avatar_file", ['file' => $user->getAvatarBrut()]);

            return action("Controller@storage_public_avatar_file", ['file' => 'default_avatar.jpg']);
        }

        if($u != "this" && $this->model()->user->userExist($u) && $this->model()->user->getUserById(intval($u))['avatar'] != null)
            return action("Controller@storage_public_avatar_file", ['file' => $this->model()->user->getUserById(intval($u))['avatar']]);

        return action("Controller@storage_public_avatar_file", ['file' => 'default_avatar.jpg']);
    }

    /**
     * Determine user
     */
    public function userAvatarView()
    {
        return setViewForTheme("Users.Profile.userAvatar");
    }
}