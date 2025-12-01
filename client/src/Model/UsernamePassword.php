<?php

namespace App\Model;

use Symfony\Component\Validator\Constraints as Assert;

class UsernamePassword
{
    #[Assert\Email]
    public string $email;
    public string $password;
}
