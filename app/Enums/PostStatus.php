<?php

namespace App\Enums;

enum PostStatus: string
{
    case WAITING = 'waiting';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}