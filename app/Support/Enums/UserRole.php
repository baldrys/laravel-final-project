<?php

namespace App\Support\Enums;

use BenSampo\Enum\Enum;

final class UserRole extends Enum
{
    const Customer = "Customer";
    const StoreUser = "StoreUser";
    const Admin = "Admin";
}
