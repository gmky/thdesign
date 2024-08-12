<?php

namespace App;

enum UserRole : string
{
    case ADMIN = 'ADMIN';
    CASE NORMAL_USER = 'NORMAL_USER';
    CASE EDITOR = "EDITOR";
}
