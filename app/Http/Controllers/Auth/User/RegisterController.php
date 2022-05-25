<?php

namespace App\Http\Controllers\Auth\User;

use Anhskohbo\NoCaptcha\NoCaptcha;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    public function __construct()
    {
        if(env('MODULE_AUTH') == false)
            return abort(404);
    }

    public function userRegister()
    {
        return $this->view('Users.Register.home', $this->lang('messages.Route.CMSRegisterPage'));
    }

    public function ajax_userRegister(Request $request)
    {
        $inputs = $request->all();
        $config = $this->model()->config->first();

        $validate = Validator::make($inputs, [
            "username" => "required",
            "email" => "required",
            "password" => "required|min:8",
            "rpassword" => "required|min:8|same:password",
            "conditions" => ($config['cg']) ? "required" : "",
        ], $this->lang('validator'));

        $validate->setAttributeNames([
            "username" => "pseudonyme",
            "email" => "email",
            "password" => __('messages.PASSWORD'),
            "rpassword" => __('messages.RPASSWORD'),
            "g-recaptcha-response" => "recaptcha"
        ]);

        if($validate->fails())
            return $this->sendJsonRequest('error', $validate->errors()->messages());

        $captcha = new NoCaptcha(env('NOCAPTCHA_SECRET'), env('NOCAPTCHA_SITEKEY'));

        if($config['captcha'] && !$captcha->verifyResponse($inputs['g-recaptcha-response']))
            return $this->sendJsonRequest('error', $this->lang('messages.Pages.Register.Error.Recaptcha is not valid'));

        if($this->model()->user->where('pseudo', $inputs['username'])->count())
            return $this->sendJsonRequest('warning', $this->lang('validator.TXController.Register.Pseudo Already Exist'));

        if($this->model()->user->where('email', $inputs['email'])->count())
            return $this->sendJsonRequest('warning', $this->lang('validator.TXController.Register.Email Already Exist'));

        $this->auth()->setRegister(['pseudo' => $inputs['username'], 'email' => $inputs['email'], 'password' => $inputs['password']], false);

        $this->auth()->setLogin($this->model()->user->where('pseudo', $inputs['username'])->where('email', $inputs['email'])->first()['id']);

        return $this->sendJsonRequest('success', $this->lang('messages.Pages.Register.Success'));
    }
}
