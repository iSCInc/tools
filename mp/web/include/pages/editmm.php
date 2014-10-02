<?php
/*
 * editmmpage.php
 * Copyright (C) 2010-12 by Robin Krahl, Merlissimo, Euku and others
 *
 * This file is published under the terms of the MIT license
 * (http://www.opensource.org/licenses/mit-license.php) and the
 * LGPL (http://www.gnu.org/licenses/lgpl.html).
 *
 * For more information, see http://toolserver.org/~dewpmp.
 */

/**
 * This page is used for editing the mentee-mentor relationship.
 */
class EditMMPage implements Page
{
  /**
   * handle of the MP database
   */
  private $db;
  /**
   * access object
   */
  private $access;

  
  /**
   * Constructor.
   * @param Database $db_mp handle of the MP database
   * @param Access $access Access object
   */
  public function __construct($db_mp, $access)
  {
    $this->db     = $db_mp;
    $this->access = $access;
  }

  /**
   * Returns information on what to display.
   * @return mixed if succesful: array:  information on what to display
   *               else:         string: error string
   */
  public function display()
  {
    if (!$this->access->is_editor())
    {
      return "Du musst angemeldet sein und die entsprechenden Rechte besitzen, um Einträge bearbeiten zu können.";
    }

    $rv = array();
    $rv['title']   = "Mentee-Mentor-Beziehung bearbeiten";
    $rv['heading'] = "Mentee-Mentor-Beziehung bearbeiten";
    $rv['page']    = "editmm";
    $rv['data']    = array();

    $mentor_id = '';
    if (isset($_GET['mentor_id']))
    {
      $mentor_id = $_GET['mentor_id'];
    }
    $mentee_id = '';
    if (isset($_GET['mentee_id']))
    {
      $mentee_id = $_GET['mentee_id'];
    }
    if ($mentee_id === '' && $mentor_id === '')
    {
      return "Du musst mindestens entweder die ID des Neulings oder die des Mentors angeben (<tt>mentee_id</tt> oder <tt>mentor_id</tt>).";
    }

    $start = '';
    if (isset($_GET['start']))
    {
      $start = $_GET['start'];
    }
    $end = '';
    if (isset($_GET['end']))
    {
      $end = $_GET['end'];
    }
    $create_new_item = FALSE;
    if (isset($_GET['create']))
    {
      $create_new_item = $_GET['create'] == 'new';
    }
    
    // do we want to edit an old or create a new item
    if ($create_new_item) {
       // create a virtual item
       $pos[0]['mm_mentor_id'] = '';
       $pos[0]['mm_mentee_id'] = $mentee_id;
       $pos[0]['mm_start'] = '';
       $pos[0]['mm_stop'] = '';
       $pos[0]['mm_type'] = 0;
    } else {
       // find an existing one
       $pos = $this->db->get_mm_items($mentor_id, $mentee_id, $start, $end);
    }

    if (count($pos) == 0)
    {
      return "Es existiert keine Mentee-Mentor-Beziehung mit diesen Eigenschaften.";
    }

    if (count($pos) == 1 && isset($_POST['start']))
    {
      // write mode
      if (!(isset($_POST['start']) && isset($_POST['end'])))
      {
        return "Du hast ein falsches Formular verwendet.";
      }
      if (!isset($_POST['new_mentor_id']))
      {
        return "<tt>new_mentor_id</tt> nicht gesetzt!";
      }
      if (!validate_timestamp($_POST['start']))
      {
        return "<tt>start</tt> ist kein gültiger Zeitstempel!";
      }
      if (!(validate_timestamp($_POST['end']) || $_POST['end'] === ''))
      {
        return "<tt>end</tt> ist kein gültiger Zeitstempel!";
      }
      if (!isset($_POST['type']))
      {
        return "<tt>type</tt> nicht gesetzt!";
      }
      
      // Commit the change to the data base!
      if ($create_new_item)
      {
         $new_mentor_id = $_POST['new_mentor_id'];
         $this->db->add_mm_item($new_mentor_id, $mentee_id, $_POST['start'], $_POST['end'], $_POST['type']);
         // log
         $new_mentor_name = $this->db->getMentorById($new_mentor_id);
         $mentee_name = $this->db->getMenteeById($mentee_id);
         $this->db->log($this->access->user(), "Added mentee-mentor relation between '$new_mentor_name[1]' and '$mentee_name[1]'", "add_menteementor", $mentee_id);
      } else {
         $this->db->update_mm_item($mentor_id, $mentee_id, $start, $end, $_POST['new_mentor_id'], $_POST['start'], $_POST['end'], $_POST['type']);
         // log
         $mentor_name = $this->db->getMentorById($mentor_id);
         $mentee_name = $this->db->getMenteeById($mentee_id);
         $this->db->log($this->access->user(), "Updated mentee-mentor relation between '$mentor_name[1]' and '$mentee_name[1]'", "update_menteementor", $mentee_id);
      }

      $rv['page'] = "editmm_result";
    }
    else
    {
      if (!$create_new_item)
      {
        foreach ($pos as $key => $p)
        {
          $mentor = $this->db->getMentorById($p['mm_mentor_id']);
          $mentee = $this->db->getMenteeById($p['mm_mentee_id']);
          $p['mentor_user_name'] = $mentor['mentor_user_name'];
          $p['mentee_user_name'] = $mentee['mentee_user_name'];
          $p['create_new_item'] = '';
          $pos[$key] = $p;
        }
      } else
      {
          $mentee = $this->db->getMenteeById($mentee_id);
          $pos[0]['mentee_user_name'] = $mentee['mentee_user_name'];
          $pos[0]['create_new_item'] = 'new';
      }

      # don't load this if we want to see a list of users
      if (count($pos) == 1)
      {
        $rv['data']['mentee_page_history'] = $this->db->get_user_page_history($pos[0]['mentee_user_name']);
      }      
    }

    $rv['data']['pos'] = $pos;
    $rv['data']['mentors'] = $this->db->get_all_mentors();
    
    return $rv;
  }
}
