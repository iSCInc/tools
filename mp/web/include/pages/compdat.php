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

class CompDatPage implements Page
{
  # Datenbank-Handle
  private $db;

  # __construct(Database)
  # Konstruktor
  function __construct($db)
  {
    $this->db = $db;
  }

  # display()
  public function display()
  {
    $all_mentees_raw = $this->db->get_all_active_mentees();
    $all_mentees = array();
    foreach ($all_mentees_raw as $mentee)
    {
      $all_mentees[] = $mentee['mentee_user_name'];
    }
    $wp_mentees_raw = $this->db->get_all_wp_mentees();
    $wp_mentees = array();
    foreach ($wp_mentees_raw as $mentee)
    {
      $wp_mentees[] = preg_replace('/_/', ' ', $mentee['page_title']);
    }
    $all_mentors = $this->db->get_all_active_mentors();

    $all_mentees = array_unique($all_mentees);
    $wp_mentees = array_unique($wp_mentees);
    asort($all_mentees);
    asort($wp_mentees);

    $rv = array();
    $rv['title']   = "Datenvergleich";
    $rv['heading'] = 'Datenvergleich';
    $rv['page']    = "compdat";
    $rv['data']    = array();
    $rv['data']['all_mentees'] = $all_mentees;
    $rv['data']['wp_mentees'] = $wp_mentees;

    return $rv;
  }
}
