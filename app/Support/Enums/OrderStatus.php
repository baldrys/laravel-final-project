<?php

namespace App\Support\Enums;

use BenSampo\Enum\Enum;

final class OrderStatus extends Enum
{
    const Canceled = "Canceled";
    const Placed = "Placed";
    const Approved = "Approved";
    const Shipped = "Shipped";
    const Received = "Received";
}
