<?php

namespace App\Enums;

use Illuminate\Validation\Rules\Enum;

class ActionEnum extends Enum
{
   const UPDATE = 'update';
   const SAVE = 'save';
}
