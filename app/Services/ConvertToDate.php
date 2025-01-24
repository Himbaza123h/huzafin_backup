<?php

namespace App\Services;

use Carbon\Carbon;

class ConvertToDate
{
   public static function generate(string $input): string
   {
      $timestamp = intval(substr($input, 6, 13)); // Extract "1707264000000"

      // Convert milliseconds to seconds
      $timestampInSeconds = $timestamp / 1000;

      // Create a Carbon instance from the Unix timestamp
      $date = Carbon::createFromTimestamp($timestampInSeconds);

      // Now $date contains the real date
      return $date->toDateString();
   }
}
