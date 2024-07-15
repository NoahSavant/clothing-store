<?php

namespace Tests\Unit;

class BaseObjectTest
{
    public $id;

    public $picture;

    public $account_verify;

    public $verify_code;

    public $overtimed_at;

    public $deleted_at;

    public $rememberToken;

    public $user;

    public $name;

    public function save()
    {

    }

    public function listUsersMessages($a, $b)
    {
        return $this;
    }

    public function getMessages()
    {
        return 'messages';
    }

    public function send($param1, $param2)
    {
        return true;
    }

    public function insert($param1, $param2, $param3)
    {
        return $this;
    }

    public function getHangoutLink()
    {
        return 'link';
    }
}
