<?php

namespace App\Http\Controllers\Auth\User;

use App\Events\Auth\onSendForgotPassword;
use App\Http\Controllers\Controller;
use App\TModels\ResetPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ForgotController extends Controller
{
    public function __construct()
    {
        if (env('MODULE_AUTH') == false)
            return abort(404);
    }

    public function userForgot()
    {
        return $this->view('Users.Forgot.home', $this->lang('messages.Route.CMSForgotPage'));
    }

    public function post_userForgot(Request $request)
    {
        $validate = Validator::make($request->all(), [
            "email" => "required",
        ], $this->lang('validator'));

        if($validate->fails())
            return $this->sendJsonRequest('error', $validate->errors()->messages());

        $email = $request->input('email');

        if(!$this->model()->user->whereEmail($email)->count())
            return $this->sendJsonRequest('error', __('messages.wrong_info'));

        event(new onSendForgotPassword($this->model()->user->whereEmail($email)->first()));

        return $this->sendJsonRequest("success", __('messages.Updated'));
    }

    public function resetPassword($token)
    {
        if(!ResetPassword::check($token))
            return abort(404);

        return $this->view("Users.Forgot.verification", $this->lang('messages.Route.CMSForgotVerifPage'), compact('token'));
    }

    public function post_reset_password(Request $request)
    {
        $data = $request->input('data');

        $validate = Validator::make($request->all(), [
            "password" => "required|min:8",
            "password_repeat" => "required|min:8|same:password",
        ], trans('validator'));

        $validate->setAttributeNames([
            "password" => __('messages.PASSWORD'),
            "password_repeat" => __('messages.RPASSWORD'),
        ]);

        if($validate->fails())
            return $this->sendJsonRequest("error", $validate->errors()->messages());

        $user = ResetPassword::getUser($data);

        $user->resetPassword($request->input('password'));

        return $this->sendJsonRequest("success", __('messages.Updated'));
    }

}