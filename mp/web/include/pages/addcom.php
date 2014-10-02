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
 * This page can be used for adding a mentor/comentor relation.
 */
class AddComPage implements Page
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
      return "Du musst angemeldet sein und die Erlaubnis dazu haben, um Einträge hinzuzufügen.";
    }
  
    if (!isset($_GET['id']))
    {
      return "Du musst die IDs des entsprechenden Mentors angeben (<tt>id</tt>)!";
    }
    $mid  = $_GET['id'];
    if ($this->db->getMentorById($mid) == array())
    {
      return "Es exisitiert kein Mentor mit der ID $mid.";
    }
    
    $rv = array();
    $rv['data']    = array();
    $rv['data']['mid'] = $mid;
    $rv['title']   = "Co-Mentor hinzufügen";
    $rv['heading'] = "Co-Mentor hinzufügen";
    
    if (isset($_POST['cmid']))
    {
      $cmid = $_POST['cmid'];
      if ($this->db->getMentorById($cmid) == array())
      {
        return "Es existiert kein Mentor mit der ID $cmid.";
      }
      if ($this->db->exists_comentor_connection($mid, $cmid))
      {
        return "Dieser Mentor ist schon Co-Mentor.";
      }
    
      $this->db->add_comentor($mid, $cmid);
      
      $rv['data']['cmid'] = $cmid;
      $rv['page'] = 'addcom_result';

      $this->db->log($this->access->user(),
	             "Added comentor $cmid for mentor $mid",
	             'add_comentor',
		     $mid);

    }
    else
    {
      $rv['page'] = 'addcom_form'; 
      $rv['data']['mentors'] = $this->db->getMentorsByNameRegExp('.*');
    }    

    return $rv;
  }
}
