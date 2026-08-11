<?php

namespace App\Workflow\State;

class MissionState
{
    public const string START = 'start';
    public const string ASSIGNED = 'assigned';
    public const string PICKED = 'picked';
    public const string PENDING = 'pending';
    public const string READY = 'ready';
    public const string DELIVERED = 'delivered';

}
