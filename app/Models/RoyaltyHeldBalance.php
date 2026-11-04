<?php

namespace App\Models;

/**
 * RoyaltyHeldBalance Model (Proxy / Eloquent view over Employee).
 *
 * Dedicated model backing RoyaltyHeldBalanceResource to give it its own
 * distinct identity and prevent collision with EmployeeResource in Shield permissions.
 */
class RoyaltyHeldBalance extends Employee
{
    protected $table = 'employees';
}
