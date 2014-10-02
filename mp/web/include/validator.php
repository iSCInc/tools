<?php
/*
 * validator.php
 * Copyright (C) 2010 by Robin Krahl, Merlissimo and others
 * 
 * This file is published under the terms of the MIT license
 * (http://www.opensource.org/licenses/mit-license.php) and the
 * LGPL (http://www.gnu.org/licenses/lgpl.html).
 *
 * For more information, see http://toolserver.org/~dewpmp.
 */

/**
 * Validate a time string. Format should be: YYYY-MM-DD. 
 * @param string $timestamp the timestamp to validate
 * @returns bool ‘true’ if the timestamp is valid
 */
function validate_timestamp($timestamp)
{
  $m = preg_match("/\d\d\d\d-\d\d-\d\d/", $timestamp);
  if ($m)
  {
    $parts = preg_split("/-/", $timestamp);
    $ye = (int) $parts[0];
    $mo = (int) $parts[1];
    $da = (int) $parts[2];
    if ($mo > 12 || $mo < 1)
      $m = false;
    if ($da > 31 || $da < 1)
      $m = false;
    if ($ye < 2000)
      $m = false;
  }
  return (bool) $m;
}
?>
