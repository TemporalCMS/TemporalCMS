<?php

namespace App\Http\Traits\Auth;

use App\Http\Controllers\Auth\TXController as Auth;

class Base extends Auth {

    private $user = false;

    public function getId()
    {
        if($this->getUser())
            return $this->getUser()->id;

        return null;
    }

    public function getUsername()
    {
        if($this->getUser())
            return $this->getUser()->pseudo;

        return null;
    }

    public function getEmail()
    {
        if($this->getUser())
            return $this->getUser()->email;

        return null;
    }

    public function getMoney()
    {
        if($this->getUser())
            return $this->getUser()->money;

        return null;
    }

    public function getUuid()
    {
        if($this->getUser())
            return $this->getUser()->uuid;

        return null;
    }

    public function get2FA()
    {
        if($this->getUser())
            return $this->getUser()->{"2fa"};

        return null;
    }

    public function isBanned()
    {
        if($this->getUser())
            return $this->getUser()->banned ? true : false;

        return null;
    }

    public function getBannedReason()
    {
        if($this->getUser())
            return $this->getUser()->banned_reason;

        return null;
    }

    public function isConfirmed()
    {
        if($this->getUser())
            return $this->getUser()->confirm_email ? true : false;

        return null;
    }

    public function hasEnable2Fa()
    {
        if($this->getUser())
            return ($this->getUser()->{"2fa"} != null) ? true : false;

        return null;
    }

    public function getAvatarBrut()
    {
        if($this->getUser())
            return $this->getUser()->avatar;

        return null;
    }

    public function getCreatedAccount()
    {
        if($this->getUser())
            return $this->getUser()->created_at;

        return null;
    }

    public function getLastUpdate()
    {
        if($this->getUser())
            return $this->getUser()->updated_at;

        return null;
    }

    public function hasPermission($perm)
    {
        if($this->getUser())
            return $this->getUser()->hasPermission($perm);

        return null;
    }

    public function hasRole($role)
    {
        if($this->getUser())
            return $this->getUser()->hasRole($role);

        return null;
    }

    public function hasConfirmed2fa()
    {
        if($this->getUser())
            return (session()->has('tempo_access_twoapp')) ? true : false;

        return null;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        if($this->isLogin())
            $this->user = $this->getData();

        return $this->user;
    }

    public function isLogin()
    {
        return parent::isLogin();
    }

}