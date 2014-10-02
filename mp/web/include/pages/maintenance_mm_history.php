<?php
/*
 * Copyright (C) 2014 by Euku
 *
 * This file is published under the terms of the MIT license
 * (http://www.opensource.org/licenses/mit-license.php) and the
 * LGPL (http://www.gnu.org/licenses/lgpl.html).
 *
 * For more information, see http://tools.wmflabs.org/mp.
 */

class MaintenanceMmHistoryPage implements Page
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function display()
  {
    $end_time = isset($_GET["end_time"]) ? $_GET["end_time"] : '';
    if ($end_time != '' and !validate_timestamp($end_time))
    {
        return "Ungültiger Zeitstempel";
    }
    $limit = 200;
    if (isset($_GET["limit"]))
    {
      $limit = (int) $_GET["limit"];
      if ($limit > 5000)
      {
          $limit = 5000;
      }
    }
    $sort_by = 'start';
    $rv = array();
    $rv['title']   = 'Wartungsseite: Historisch Betreuungen';
    $rv['heading'] = 'Wartungsseite: Historische Betreuungen';
    $rv['page']    = 'maintenance_mm_history';
    $rv['data'] = array();
    $rv['data']['end_time'] = $end_time;
    $rv['data']['limit']   = $limit;
    // db query
    $db_query = $this->db->get_mentor_mentee_history($end_time, $limit +1);
    $rv['data']['history'] = $db_query;
    // get the next_timestamp
    $last_timestamp = '';
    if (sizeof($db_query) != 0) {
       $last_timestamp = $db_query[ sizeof($db_query)-1 ]['event_time'];
    }
    $rv['data']['next_end_time'] = $last_timestamp;
    // remove the last element, we didn't asked for
    array_pop($rv['data']['history']);
    return $rv;
  }
}
