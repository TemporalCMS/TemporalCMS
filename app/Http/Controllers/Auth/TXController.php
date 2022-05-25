<?php

namespace App\Http\Controllers\Auth;

use App\Events\Auth\afterLogin;
use App\Events\Auth\afterRegister;
use App\Events\Auth\beforeLogin;
use App\Events\Auth\beforeRegister;
use App\Events\Auth\onLogout;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class TXController extends Controller
{
    private $request;

    public function __construct()
    {
        $this->request = request();
    }

    public function isLogin()
    {
        // Check if user isTX login by "id"
        if($this->request->session()->exists('id'))
            return true;
        return false;
    }

    public function getData()
    {
        if(!$this->isLogin())
            return false;
        return $this->model()->user->where('id', intval($this->getSessionData()['id']))->first();
    }

    public function setRegister(Array $data_user, $verification = true, $role = "member")
    {
        // Add beforeRegister event, execute this before register user
        event(new beforeRegister($data_user, $verification, $role));

        // Step 1 : Check if in the array $data_user, keys : "pseudo" or/and "email" or/and "password" is/are existing, else execute a new exception with error info
        if(empty($data_user['pseudo']) || empty($data_user['email']) || empty($data_user['password']))
            throw new \Exception('[App\Http\Controllers\Auth\TXController.php] Function : setRegister, pseudo or email or password param is empty');

        // Step 2 : If second parameter ($verification) is true so we check if $data_user['pseudo'] is already exists in database
        if($verification && $this->model()->user->where('pseudo', $data_user['pseudo'])->count())
            return $this->sendJsonRequest('warning', $this->lang('validator')['TXController']['Register']['Pseudo Already Exist']);

        // Step 2 (bis) : If second parameter ($verification) is true so we check if $data_user['email'] is already exists in database
        if($verification && $this->model()->user->where('email', $data_user['email'])->count())
            return $this->sendJsonRequest('warning', $this->lang('validator')['TXController']['Register']['Email Already Exist']);

        // Step 3 : check all mandatory condition (BEGIN)
        if(!isset($data_user['money']))
            $data_user['money'] = 0.00;

        if(!isset($data_user['confirm_email']))
            $data_user['confirm_email'] = 0;

        if(!isset($data_user['banned']))
            $data_user['banned'] = 0;

        if(!isset($data_user['created_at']))
            $data_user['created_at'] = now();

        // Step 3 : (END)

        // Step 4 : Hash password in BCRYPT
        $password_hash = Hash::make($data_user['password']);

        // Step 5 : Generate fake UUID for API(s) if user isn't premium AND set premium to 0
        $get_uuid = gen_uuid();

        // Step final : Create a user in database
        $this->model()->user->insert(['pseudo' => $data_user['pseudo'], 'password' => $password_hash, 'email' => $data_user['email'], 'confirm_email' => $data_user['confirm_email'], 'banned' => $data_user['banned'], 'created_at' => $data_user['created_at'], 'money' => $data_user['money'],'uuid' => $get_uuid]);

        $response = $this->model()->user->where('pseudo', $data_user['pseudo'])->where('email', $data_user['email'])->count();
        $user = $this->model()->user->where('pseudo', $data_user['pseudo'])->where('email', $data_user['email'])->first();

        $user->attachRole($role);

        // Add afterRegister event, execute this after register user
        event(new afterRegister($user, $verification, ($response) ? 1 : 0, $role));
    }

    public function checkLoginUsernameAndPassword($username, $password)
    {
        // This function return true if is good else false
        if(!$this->model()->user->where('pseudo', $username)->count())
            return false;

        if(!Hash::check($password, $this->model()->user->where('pseudo', $username)->first()['password']))
            return false;

        return true;
    }

    public function setLogin($user_id)
    {
        // Add beforeLogin event, execute this before login user
        event(new beforeLogin($user_id));

        // Check if user_id param is empty
        if(empty($user_id))
            throw new \Exception('[App\Http\Controllers\Auth\TXController.php] Function : setLogin, user_id param is empty');

        // Check if this user is already exists in the database
        if(!$this->model()->user->where('id', intval($user_id))->count())
            throw new \Exception('[App\Http\Controllers\Auth\TXController.php] Function : setLogin, user_id param is not found in the database');

        // Get all data of user
        $user = $this->model()->user->where('id', intval($user_id))->first();

        // Create a session user with data
        $this->request->session()->put(['id' => $user_id, 'pseudo' => $user->pseudo, 'password' => $user->password, 'created_at' => $user->created_at]);
        $this->request->session()->save();

        // Add afterLogin event, execute this after login user
        event(new afterLogin($user));
    }

    public function logout($url = "/")
    {
        // Add onLogout event, execute this before logout user
        event(new onLogout(['user' => ['id' => $this->getSessionData()['id'], 'pseudo' => $this->getSessionData()['pseudo']]]));

        // Check if user is login so flush all session data
        if($this->isLogin())
            $this->request->session()->flush();

        return redirect()->to($url)->send();
    }

    public function getSessionData()
    {
        return $this->request->session()->all();
    }

    /**
     * @return mixed
     */
    public function setBackUrl()
    {
        $this->request->session()->put('url.intended', url()->previous());
    }

    /**
     * @return mixed
     */
    public function back()
    {
        if(!$this->request->session()->has('url.intended')) {
            $this->setBackUrl();
        }

        return $this->request->session()->get('url.intended');
    }
}