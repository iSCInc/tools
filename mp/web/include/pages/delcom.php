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

/**
 * This page can be used for deleting a mentor/comentor relation.
 */
class DelComPage implements Page
{
  /**
   * An instance of the Wikipedia database object.
   */
  private $db;
  /**
   * An instance of the access class to determine whether
   * the current user is logged in.
   */
  private $access;

  /**
   * Constructs a new instance of this class.
   * @param Database db 
   *        Wikipedia database access
   * @param Access access
   *        login information
   */
  public function __construct($db, $access)
  {
    $this->db = $db;
    $this->access = $access;
  }
  
  /**
   * Aggregates data for displaying this page.
   * @returns array data about the page to display
   */
  public function display()
  {
    if (!$this->access->is_editor())
    {
      return "Du musst angemeldet sein und die Erlaubnis dazu haben, um Einträge löschen zu können.";
    }
  
    if (!(isset($_GET['mid']) && isset($_GET['cmid'])))
    {
      return "Du musst die IDs der beiden Mentoren angeben (<tt>mid</tt> und <tt>cmid</tt>)!";
    }
    $mid  = $_GET['mid'];
    $cmid = $_GET['cmid'];
    if (!$this->db->exists_comentor_connection($mid, $cmid))
    {
      return "Es exisitiert kein passender Eintrag, der gelöscht werden könnte!";
    }
    
    $this->db->delete_comentor($mid, $cmid);
    
    $rv = array();
    $rv['page']    = "delcom";
    $rv['data']    = array();
    $rv['data']['mid'] = $mid;
    $rv['title']   = "Co-Mentor löschen";
    $rv['heading'] = "Co-Mentor löschen";

    $this->db->log($this->access->user(), "Removed comentor $cmid for mentor $mid", "remove_comentor", $mid);

    return $rv;
  }
}
