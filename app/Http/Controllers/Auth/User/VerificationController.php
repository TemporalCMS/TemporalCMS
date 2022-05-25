<?php

namespace App\Http\Controllers\Auth\User;

use App\Events\Auth\afterVerify2fa;
use App\Events\Auth\beforeVerify2fa;
use App\Http\Controllers\Controller;
use App\TModels\UserConfirmAccount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use PragmaRX\Google2FAQRCode\Google2FA;

class VerificationController extends Controller
{
    private $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();

        if(env('MODULE_AUTH') == false)
            return abort(404);
    }

    public function userDoubleAuth()
    {
        if($this->auth()->hasConfirmed2fa())
            return abort(404);

        return $this->view('Users.TwoFA.home', $this->lang('messages.Route.CMSTwoFAVerifPage'));
    }

    public function ajax_double_auth_setup(Request $request)
    {
        $code = htmlspecialchars($request->input('code'));

        event(new beforeVerify2fa($code, $this->auth()->getUser()));

        if(empty($code))
            return $this->sendJsonRequest("error", __('messages.Form.FieldEmpty'));

        $this->google2fa->setEnforceGoogleAuthenticatorCompatibility(true);
        $key = $this->auth()->get2FA();

        if(!$this->google2fa->verifyKey($key, $code))
            return $this->sendJsonRequest("error", __('theme_default.Pages.Profile.ErrorTwoFACode'));

        session()->push('tempo_access_twoapp', now());

        event(new afterVerify2fa($code, $this->auth()->getUser()));

        return $this->sendJsonRequest("success", __('messages.Updated'));
    }

    public function userConfirmAccount($token)
    {
        if(!UserConfirmAccount::whereToken($token)->count())
            return abort(404);

        $validation = UserConfirmAccount::whereToken($token)->first();

        $this->model()->user->getUserById($validation->user_id)->update(['confirm_email' => 1]);
        $validation->delete();

        return redirect()->route("CMSLoginPage")->send();
    }
}
