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

class MaintenanceExMentorPage implements Page
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function display()
  {
    $rv = array();
    $rv['title']   = 'Wartungsseite: Ex-Mentor';
    $rv['heading'] = 'Wartungsseite: Ex-Mentor';
    $rv['page']    = 'maintenance_ex_mentor';
    $rv['data'] = array();
    $rv['data']['mentor'] = $this->db->get_all_active_mentors_without_category();
    return $rv;
  }
}
