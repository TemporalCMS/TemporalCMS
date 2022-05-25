<?php

use App\System\Module\Payment\PaymentMethod;

if(!function_exists('plugin')) {
    function plugin()
    {
        return app(\App\System\Extensions\Plugin\Plugin::class);
    }
}

if(!function_exists('theme')) {
    function theme()
    {
        return app(\App\System\Extensions\Theme\Theme::class);
    }
}

if(!function_exists('game')) {
    function egame()
    {
        return app(\App\System\Extensions\Game\Game::class);
    }
}


if(!function_exists('tx_auth')) {
    function tx_auth()
    {
        return tx()->auth();
    }
}

if(!function_exists('tx_payment')) {
    function tx_payment()
    {
        return app(PaymentMethod::class);
    }
}

if(!function_exists('avatar')) {
    function avatar($u = "this")
    {
        if(egame()->isDefault()) {

            if(!user()->hasAvatar()) {
                return action("Controller@storage_public_avatar_file", ['file' => 'default_avatar.jpg']);
            }

            return action("Controller@storage_public_avatar_file", ['file' => user()->avatar]);
        }

        return egame()->app()->userAvatar($u);
    }
}

if(!function_exists('avatar_view')) {
    function avatar_view()
    {
        if(egame()->isDefault())
            return setViewForTheme("Users.Profile.userAvatar");

        return egame()->app()->userAvatarView();
    }
}