<?php

namespace App\Enums;

enum WorkOrderStatus: string
{
    case Open = 'open';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
