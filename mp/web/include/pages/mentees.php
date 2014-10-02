<?php
/*
 * Copyright (C) 2010 by Robin Krahl, Merlissimo and others
 *
 * This file is published under the terms of the MIT license
 * (http://www.opensource.org/licenses/mit-license.php) and the
 * LGPL (http://www.gnu.org/licenses/lgpl.html).
 *
 * For more information, see http://toolserver.org/~dewpmp.
 */

class MenteesPage implements Page
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function display()
  {
    $rv = array();
    $rv['title']   = 'Menteeübersicht';
    $rv['heading'] = 'Liste aller momentan betreuten Benutzer';
    $rv['page']    = 'mentees';
    $rv['data'] = array();
    $rv['data']['mentees'] = $this->db->get_all_active_mentees();
    foreach ($rv['data']['mentees'] as $id => $m)
    {
      $rv['data']['mentees'][$id]['recent_edit'] = $this->db->has_recent_edit($m['mentee_user_id']);
    }
    return $rv;
  }
}