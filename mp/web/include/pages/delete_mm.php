<?php
/*
 * Copyright (C) 2010 by Euku, Robin Krahl, Merlissimo and others
 *
 * This file is published under the terms of the MIT license
 * (http://www.opensource.org/licenses/mit-license.php) and the
 * LGPL (http://www.gnu.org/licenses/lgpl.html).
 *
 * For more information, see http://toolserver.org/~dewpmp.
 */

/**
 * This page deletes a mentee_mentor relation.
 */
class DeleteMmPage implements Page
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
      return "Du musst angemeldet sein, um etwas zu löschen.";
    }
    if (!isset($_POST['mentee_id']) or !isset($_POST['mentor_id']) or !validate_timestamp($_POST['mm_start']))
    {
      return "Parameter waren unvollständig! mentor_id: " . $_POST['mentor_id'] . ", mentee_id: " . $_POST['mentee_id'];
    }
    $mentee_id  = (int) $_POST['mentee_id'];
    $mentor_id  = (int) $_POST['mentor_id'];
    $mm_start   = $_POST['mm_start'];

    $rv['data']    = array();
    $rv['title']     = "Mentee-Mentor-Beziehung gelöscht";
    $rv['heading']   = "Mentee-Mentor-Beziehung gelöscht";
    $rv['data']['mentee_id'] = $mentee_id;
    $rv['data']['mentor_id'] = $mentor_id;
 
    if (!$this->db->delete_mm_item($mentor_id, $mentee_id, $mm_start))
    {
        return "Fehler beim Löschen.";
    }
    $this->db->log($this->access->user(),
	             "Relation gelöscht $mentor_id -> $mentee_id ($mm_start)",
	             'delete_mm', $mentee_id);
    $rv['page'] = "delete_mm_result";
    return $rv;
  }
}
