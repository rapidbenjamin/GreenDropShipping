<?php

namespace App\Libraries;

class OrderStatus
{
    public const Outstanding = 1;
    public const Paid = 2;
    public const Refounded = 3;
    public const NewOrder = 4;
    public const InProcessOrder = 5;
    public const Shipped = 6;
    public const Delivered = 7;
    public const Returned = 8;
}