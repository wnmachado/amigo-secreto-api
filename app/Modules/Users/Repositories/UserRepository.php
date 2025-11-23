<?php

namespace App\Modules\Users\Repositories;

use App\Modules\Users\Models\Users;

class UserRepository
{
    /**
     * Create a new event.
     */
    public function create(array $data): Users
    {
        $user = Users::where('email', $data['email'])->first();
        if ($user) {
            return $user;
        }
        return Users::create($data);
    }
}
