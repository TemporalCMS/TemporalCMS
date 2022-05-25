<?php

namespace App\Http\Controllers\Auth\User;

use App\Events\Auth\onSendConfirmMail;
use App\Http\Controllers\Controller;
use App\TModels\User;
use App\TModels\UserPointsHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use PragmaRX\Google2FAQRCode\Google2FA;

class ProfileController extends Controller
{
    private $google2fa;

    public function __construct()
    {
        User::unguard();

        $this->google2fa = new Google2FA();

        if(env('MODULE_AUTH') == false)
            return abort(404);
    }

    public function home()
    {
        return $this->view('Users.Profile.home', $this->lang('messages.Route.CMSProfilePage', ['user' => $this->auth()->getUsername()]));
    }

    public function logout()
    {
        return $this->auth()->logout();
    }

    public function ajax_edit_profile(Request $request)
    {
        $email = htmlspecialchars($request->input('email'));
        $password = htmlspecialchars($request->input('password'));

        if(empty($email) && empty($password))
            return $this->sendJsonRequest("error", __('theme_default.Pages.Profile.NeedOneInput'));

        if(!empty($email)) {
            if($this->model()->user->whereEmail($email)->count() && $email != user()->email)
                return $this->sendJsonRequest("error", __('theme_default.Pages.Profile.AlreadyExistEmail'));

            $this->model()->user->getUser()->update(["email" => $email, "updated_at" => now()]);
        }

        if(!empty($password))
            $this->model()->user->getUser()->update(["password" => Hash::make($password), "updated_at" => now()]);

        return $this->sendJsonRequest("success", __('messages.Updated'));
    }

    public function ajax_active_2fa(Request $request)
    {
        $code = htmlspecialchars($request->input('code'));
        $key = $request->input('key');

        if(empty($code))
            return $this->sendJsonRequest("error", __('messages.Form.FieldEmpty'));

        $this->google2fa->setEnforceGoogleAuthenticatorCompatibility(true);

        if(!$this->google2fa->verifyKey($key, $code))
            return $this->sendJsonRequest("error", __('theme_default.Pages.Profile.ErrorTwoFACode'));

        $this->model()->user->getUser()->update(['2fa' => $key]);

        session()->push('tempo_access_twoapp', now());

        return $this->sendJsonRequest("success", __('messages.Updated'));
    }

    public function ajax_disable_2fa()
    {
        $this->model()->user->getUser()->update(['2fa' => null]);

        return redirect()->action('Auth\User\ProfileController@home')->with('success', __('theme_default.Pages.Profile.TwoFADesactiveMsg'));
    }

    public function ajax_load_2fa()
    {
        $google2fa = $this->google2fa;

        $google2fa->setEnforceGoogleAuthenticatorCompatibility(true);

        $twofa_key = $google2fa->generateSecretKey();

        $twofa_img = $google2fa->getQRCodeInline(
            $this->model()->config->first()['website_name'],
            $this->auth()->getEmail(),
            $twofa_key
        );

        return response()->json(['img' => $twofa_img, 'key' => $twofa_key])->getContent();
    }

    public function ajax_import_avatar(Request $request)
    {
        $avatar = $request->file("avatar");

        if($avatar == null)
            return $this->sendJsonRequest("error", __('theme_default.Pages.Profile.AvatarNotImported'));

        if(!in_array($avatar->getClientOriginalExtension(), ['gif', 'png', 'jpg']))
            return $this->sendJsonRequest("error", __('theme_default.Pages.Profile.AvatarExtProblem'));

        if($avatar->getSize() > 9000000)
            return $this->sendJsonRequest("error", __('theme_default.Pages.Profile.AvatarSizeProblem'));

        $avatar_id = $this->auth()->getId() . "." . $avatar->getClientOriginalExtension();

        $avatar->move(storage_path("app/public/Avatar"), $avatar_id);

        $this->model()->user->getUser()->update(['avatar' => $avatar_id]);

        return $this->sendJsonRequest("success", __('messages.Updated'));
    }

    public function ajax_send_money(Request $request)
    {
        $pseudo = htmlspecialchars($request->input('pseudo'));
        $amount = floatval(htmlspecialchars($request->input('money')));

        $validator = Validator::make($request->all(), [
            "pseudo" => "required",
            "money" => "required"
        ], $this->lang('validator'));

        $validator->setAttributeNames([
            "money" => site_config('pb')
        ]);

        if($validator->fails())
            return $this->sendJsonRequest('error', $validator->errors()->messages());

        if(!$this->model()->user->where('pseudo', $pseudo)->count())
            return $this->sendJsonRequest("error", __('theme_default.Pages.Profile.pseudoDontExist'));

        $user = $this->model()->user->where('pseudo', $pseudo)->first();

        $sendMoney = $this->model()->user->getUser()->sendMoneyTo($user->id, $amount, true);

        switch($sendMoney) {
            case 1:
                return $this->sendJsonRequest("error", __('theme_default.Pages.Profile.amountGivedIsNull'));
                break;
            case 2:
                return $this->sendJsonRequest("error", __('theme_default.Pages.Profile.userDontHaveCurrentAmount', ['pb' => site_config('pb')]));
                break;
            case 3:
                return $this->sendJsonRequest("error", __('theme_default.Pages.Profile.userItsYou', ['pbs' => site_config('pbs')]));
                break;
            case 200:
                UserPointsHistory::add(user()->id, $user->id, $amount);
                return $this->sendJsonRequest("success", __('messages.Updated'));
                break;
            default:
                return $this->sendJsonRequest("error", __('messages.Error.InternalError'));
                break;
        }
    }

    public function ajax_edit_profile_confirm_email()
    {
        if(!site_config('ConfirmEmail'))
            return abort(404);

        if(user()->hasNotConfirmAccount())
            return abort(404);

        event(new onSendConfirmMail(user()));

        return redirect()->route('CMSProfilePage')->with("success", __('messages.Updated'));
    }
}
