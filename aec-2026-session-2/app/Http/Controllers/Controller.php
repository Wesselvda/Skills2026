<?php

namespace App\Http\Controllers;

use App\Models\ApplicationUser;

abstract class Controller
{
    protected function currentUser(): ApplicationUser
    {
        return ApplicationUser::findOrFail(session('user_id'));
    }
}
