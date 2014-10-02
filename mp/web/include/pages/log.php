<?php
/*
 * logpage.php
 * Copyright (C) 2010 by Robin Krahl, Merlissimo and others
 *
 * This file is published under the terms of the MIT license
 * (http://www.opensource.org/licenses/mit-license.php) and the
 * LGPL (http://www.gnu.org/licenses/lgpl.html).
 *
 * For more information, see http://toolserver.org/~dewpmp.
 */

/**
 * This page is displays the latest 50 log entries.
 */
class LogPage implements Page
{
  /**
   * Database handle.
   */
  private $db;

  /**
   * Constructor.
   */
  function __construct($db)
  {
    $this->db = $db;
  }

  /**
   * Aggregates data for displaying this page.
   * @returns array data about the page to display
   */
  public function display()
  {
    $rv = array();
    $rv['page']    = "log";
    $rv['data']    = array();
    $rv['title']   = "Log | Mentorenprogramm auf dem Wikimedia-Toolserver";
    $rv['heading'] = 'Letzte Änderungen';
    $rv['data']    = array();
    $rv['data']['log_entries'] = $this->db->get_log_entries();
    return $rv;
  }
}
