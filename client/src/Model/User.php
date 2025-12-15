<?php

namespace App\Model;

use AutoMapper\Attribute\Mapper;

#[Mapper(source: 'array', target: 'array')]
class User
{
    public string $username;
    public bool $isPublic = false;
}
