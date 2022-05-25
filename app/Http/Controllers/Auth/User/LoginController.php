<?php

namespace App\Http\Controllers\Auth\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    public function __construct()
    {
        if(env('MODULE_AUTH') == false)
            return abort(404);
    }

    public function userLogin()
    {
        return $this->view('Users.Login.home', $this->lang('messages.Route.CMSLoginPage'));
    }

    public function ajax_userLogin(Request $request)
    {
        $inputs = $request->all();

        $validator = Validator::make($inputs, [
            "username" => "required",
            "password" => "required"
        ], $this->lang('validator'));

        $validator->setAttributeNames([
            "username" => "pseudonyme",
            "password" => __('messages.PASSWORD'),
        ]);

        if($validator->fails())
            return $this->sendJsonRequest('error', $validator->errors()->messages());

        if(!$this->model()->user->where('pseudo', $inputs['username'])->count())
            return $this->sendJsonRequest('warning', $this->lang('messages.Pages.Login.Error.Username is not valid', ['username' => $inputs['username']]));

        if(!$this->auth()->checkLoginUsernameAndPassword($inputs['username'], $inputs['password']))
            return $this->sendJsonRequest('warning', $this->lang('messages.Pages.Login.Error.Password is not valid'));

        $user = $this->model()->user->where('pseudo', $inputs['username'])->first();

        if($user->banned)
            return $this->sendJsonRequest('error', $this->lang('messages.Pages.Login.Error.You are banned', ['reason' => $user->banned_reason]));

        $this->auth()->setLogin($user->id);

        return $this->sendJsonRequest('success', $this->lang('messages.Pages.Login.Success'));
    }
}
