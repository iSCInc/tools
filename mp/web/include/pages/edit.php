<?php
/*
 * editpage.php
 * Copyright (C) 2010 by Robin Krahl, Merlissimo and others
 * 
 * This file is published under the terms of the MIT license
 * (http://www.opensource.org/licenses/mit-license.php) and the
 * LGPL (http://www.gnu.org/licenses/lgpl.html).
 *
 * For more information, see http://toolserver.org/~dewpmp.
 */

/**
 *  This class provides pages to edit the stored data.
 */
class EditPage implements Page
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
	 * Returns information on what to display.
	 * @return mixed if succesful: array:  information on what to display
	 *               else:         string: error string
	 */
  public function display() 
  {
		if (!$this->access->is_editor())
			return "Du musst angemeldet sein und die Erlaubnis dazu haben, um Einträge ändern zu können.";
		
		$what = '';
		if (isset($_GET['what']))
			$what = trim($_GET['what']);
		if (empty($what))
			return "Du musst angeben, was du bearbeiten willst (<tt>what</tt>).";
		
		$id = '';
		if (isset($_GET['id']))
			$id = trim($_GET['id']);
		if (empty($id))
			return "Du musst angeben, wen du bearbeiten willst (<tt>id</tt>).";
			
		$rv = array();
		$rv['data'] = array();
		if ($what == 'mentor')
		{
			if (isset($_POST['mentor_user_name']))
			{
				# Bearbeitung anwenden
				$rv['page'] = 'edit_mentor_result';
				if (!isset($_POST['mentor_in']) ||
				    !isset($_POST['mentor_out']) ||
				    !isset($_POST['mentor_award_level']) ||
				    !isset($_POST['mentor_remark']))
					return "Du hast ein falsches Formular verwendet.";
				$mentor_user_name      = trim($_POST['mentor_user_name']);
				$mentor_in             = trim($_POST['mentor_in']);
				$mentor_out            = trim($_POST['mentor_out']);
				$mentor_has_barnstar   = false;
				if (isset($_POST['mentor_has_barnstar']))
					$mentor_has_barnstar = true;
				$mentor_award_level    = trim($_POST['mentor_award_level']);
				$mentor_remark         = trim($_POST['mentor_remark']);
				
				if ((!validate_timestamp($mentor_in) && !empty($mentor_in)) ||
				    (!validate_timestamp($mentor_out) && !empty($mentor_out)))
					return "Du hast eine invalide Zeitangabe gemacht.";
				
				if (empty($mentor_user_name))
					return "Der Benutzername darf nicht leer sein.";
					
				$this->db->updateMentor($id, $mentor_user_name, $mentor_in,
					$mentor_out, $mentor_has_barnstar, $mentor_award_level, $mentor_remark);
					
				$rv['data']['mentor_user_name'] = $mentor_user_name;
				$rv['data']['mentor_id']        = $id;
				$rv['title']   = "Mentor $mentor_user_name erfolgreich bearbeitet";
				$rv['heading'] =  "<em>$mentor_user_name</em> erfolgreich bearbeitet";

				$this->db->log($this->access->user(),
                                               'Updated mentor data for ' . $mentor_user_name,
					       'update_mentor',
					       $id);
			}
			else
			{
				# Formular anzeigen
				$rv['page'] = 'edit_mentor_form';
				$mentor = $this->db->getMentorById($id);
				if (empty($mentor))
					return "Es existiert kein Mentor mit der ID <tt>$id</tt>.";
				$rv['data']['mentor'] = $mentor;
				$rv['title']          = "Mentor {$mentor['mentor_user_name']} bearbeiten";
				$rv['heading']        = "Mentor <em>{$mentor['mentor_user_name']}</em> bearbeiten";
			}
		}
		else if ($what == 'mentee')
		{
			if (isset($_POST['mentee_user_name']))
			{
				# Aktualisieren
				$rv['page'] = 'edit_mentee_result';
				
				if (!isset($_POST['mentee_remark']))
					return "Du hast ein falsches Formular verwendet.";
				$mentee_user_name = trim($_POST['mentee_user_name']);
				$mentee_remark    = trim($_POST['mentee_remark']);
				
				if (empty($mentee_user_name))
				  return "Der Benutzername darf nicht leer sein.";
				
				$this->db->updateMentee($id,
				                        $mentee_user_name,
				                        $mentee_remark);
				$rv['data']['mentee_user_name'] = $mentee_user_name;
				$rv['data']['mentee_id']        = $id;
				$rv['title']   = "Neuling $mentee_user_name erfolgreich bearbeitet.";
				$rv['heading'] = "<em>$mentee_user_name</em> erfolgreich bearbeitet";

				$this->db->log($this->access->user(),
                                               'Updated mentee data for ' . $mentee_user_name,
					       'update_mentee',
					       $id);

			}
			else
			{
				# Formular anzeigen
				$rv['page'] = 'edit_mentee_form';
				$mentee = $this->db->getMenteeById($id);
				if (empty($mentee))
					return "Es existiert kein Mentee mit der ID <tt>$id</tt>.";
				$rv['data']['mentee'] = $mentee;
				$rv['title']          = "Neuling {$mentee['mentee_user_name']} bearbeiten";
				$rv['heading']        = "Mentee <em>{$mentee['mentee_user_name']}</em> bearbeiten";
			}
		}
		else
			return "Der Wert <tt>$what</tt> ist für <tt>\$what</tt> nicht erlaubt.";
				
    return $rv;
  }
}
