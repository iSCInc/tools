<?php
/*
 * Copyright (C) 2012 by Euku
 *
 * This file is published under the terms of the MIT license
 * (http://www.opensource.org/licenses/mit-license.php) and the
 * LGPL (http://www.gnu.org/licenses/lgpl.html).
 *
 * For more information, see http://toolserver.org/~dewpmp.
 */

class MaintenancePage implements Page
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function display()
  {
    $rv = array();
    $rv['title']   = 'Übersicht über alle Wartungsseiten';
    $rv['heading'] = 'Übersicht über alle Wartungsseiten';
    $rv['page']    = 'maintenance';
    $rv['data'] = array();
    return $rv;
  }
}
