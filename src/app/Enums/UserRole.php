<?php

namespace Paw\Enums;

enum UserRole: string {
    case CLIENT = 'client';
    case ADMIN  = 'admin';
}