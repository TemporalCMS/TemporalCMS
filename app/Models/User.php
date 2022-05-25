<?php

namespace App\Models;

use App\Interfaces\Model\ModelGettingInterface;
use App\Interfaces\Model\ModelUpdatingInterface;
use App\System\Module\TXLaratrust\Traits\LaratrustUserTrait;
use Geeky\Database\CacheQueryBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;


class User extends Model implements ModelGettingInterface, ModelUpdatingInterface
{
    use LaratrustUserTrait, CacheQueryBuilder;

    protected $fillable = ["confirm_email", "money"];

    public $auth;

    public function __construct(array $attributes = [])
    {
        $this->auth = tx_auth();

        parent::__construct($attributes);
    }

    public function reset_password()
    {
        return $this->hasMany(ResetPassword::class, 'user_id', 'id');
    }

    public function sendPartialAction($action, $user_id = "this")
    {
        return UserActions::insert(["name" => $action, "user_id" => ($user_id == "this") ? user()->id : $user_id, 'created_at' => now()]);
    }

    public function sendAllAction($action, $user_id = "this")
    {
        if(tx_model()->config->getFirstRow()->useractions_mode == 2)
            return UserActions::insert(["name" => $action, "user_id" => ($user_id == "this") ? user()->id : $user_id, 'created_at' => now()]);
    }

    public function sendMoneyTo(int $user_id, $amount, $notification = false)
    {
        if(!$this->where('id', $user_id)->count())
            return 0;

        if($amount <= 0)
            return 1;

        if($this->money < $amount)
            return 2;

        if($this->id == $user_id)
            return 3;

        $this->where('id', $this->id)->update(['money' => ($this->money - $amount)]);
        $this->where('id', $user_id)->update(['money' => ($this->getUserById($user_id)->money + $amount)]);

        if($notification) {
            $user_notif = new UserNotifications();

            $user_notif->addNotification($user_id, __('notification.user.have__receive__point', ['amount' => $amount . " " . site_config('pbs'), "pseudo" => $this->pseudo]));
            $user_notif->addNotification($this->id, __('notification.user.have__send__point', ['amount' => $amount . " " . site_config('pbs'), "pseudo" => $this->find($user_id)->pseudo]));
        }

        return 200;
    }

    public function hasAvatar()
    {
        return !is_null($this->avatar) ? true : false;
    }

    public function addMoney($amount)
    {
        return $this->update(["money" => $amount + $this->money]);
    }

    public function getActions($limit = 100, $user_id = "this")
    {
        $userAction = new UserActions();

        if($user_id != "this")
            return $userAction->where('user_id', intval($user_id))->orderByDesc('created_at')->limit($limit)->get();

        return $userAction->where('user_id', $this->getUser()->id)->orderByDesc('created_at')->limit($limit)->get();
    }

    public function addAction($name, $user_id = "this")
    {
        $userAction = new UserActions();

        if($user_id == "this")
            return $userAction->insert(['name' => $name, "user_id" => $this->auth->getId(), 'created_at' => now()]);

        return $userAction->insert(['name' => $name, 'user_id' => intval($user_id), 'created_at' => now()]);
    }

    public function getUser()
    {
        if($this->auth->isLogin())
            return $this->getUserById(intval($this->auth->getSessionData()['id']));

        return false;
    }

    public function isAdmin()
    {
        if($this->getUser())
            return ($this->getUser()->hasRole('admin')) ? true : false;

        return null;
    }

    public function isMember()
    {
        if($this->getUser())
            return ($this->getUser()->hasRole('member')) ? true : false;

        return null;
    }

    public function getUserById(int $id)
    {
        if($this->where('id', $id)->count())
            return $this->find($id);

        return null;
    }

    public function getUserByUuid(int $uuid)
    {
        if($this->where('uuid', $uuid)->count())
            return $this->where('uuid', $uuid)->first();

        return null;
    }

    public function userExist(int $id)
    {
        if($this->getUserById($id) != null)
            return true;

        return false;
    }

    public function getFirstRow($column = "id")
    {
        if(!$this->count())
            return null;

        return $this->orderBy($column, "asc")->first();
    }

    public function getLastRow($column = "id")
    {
        if(!$this->count())
            return null;

        return $this->latest($column)->first();
    }

    public function updateFirstRow($data, $column = "id")
    {
        if(!$this->count())
            return null;

        $row = $this->getFirstRow($column);

        return $row->where("id", $row->id)->update($data);
    }

    public function updateLastRow($data, $column = "id")
    {
        if(!$this->count())
            return null;

        $row = $this->getLastRow($column);

        return $row->where("id", $row->id)->update($data);
    }

    /**
     * @inheritDoc
     */
    public function resolveChildRouteBinding($childType, $value, $field)
    {
        // TODO: Implement resolveChildRouteBinding() method.
    }

    public function getUserRanks($user = "this")
    {
        if(!$this->count())
            return null;

        if($user != "this")
            $roles = $this->getUserById($user)->roles;
        else
            $roles = $this->getUser()->roles;

        $data = "";

        foreach($roles as $role) {
            $data .= __($role->display_name) . " | ";
        }

        return rtrim($data, " | ");
    }

    public function hasNotConfirmAccount()
    {
        return $this->confirm_email ? true : false;
    }

    public function resetPassword($password)
    {
        Model::unguard();

        $this->reset_password()->where('user_id', $this->id)->delete();
        $this->update(['password' => Hash::make($password), 'updated_at' => now()]);

        Model::reguard();
    }
}