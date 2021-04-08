<?php

namespace Jlab\Epas\Model;

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;

class User extends Model implements AuthenticatableContract, AuthorizableContract {

    use Authenticatable;
    use Authorizable;

    public $timestamps = false;

    public $fillable = array('id', 'username', 'firstname', 'password', 'lastname', 'is_admin');

    /**
     * Answers whether the user has global admin privilege.
     *
     * @return bool
     */
    public function isAdmin() {
        if (isset($this->is_admin)) {
            return $this->is_admin;
        }
        return false;
    }
}
