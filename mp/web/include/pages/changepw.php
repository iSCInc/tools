<?php

class ChangePWPage implements Page
{
  private $db;
  private $access;

  public function __construct($db, $access)
  {
    $this->db     = $db;
    $this->access = $access;
  }

  public function display()
  {
    if (!$this->access->logged_in())
    {
      return 'Du musst angemeldet sein, um dein Passwort ändern zu können.';
    }

    $rv = array();
    $rv['page']    = 'changepw';
    $rv['data']    = array();
    $rv['data']['what']  = 'form';
    $rv['title']   = 'Passwort ändern';
    $rv['heading'] = 'Passwort ändern';
    if (isset($_POST['new_password']))
    {
      $rv['data']['what'] = 'success';
      $new_salt = $this->access->generate_salt();
      $salted_pw = $this->access->doubleSaltedHash($_POST['new_password'], $new_salt);

      $this->db->set_hash_and_salt_for_user($this->access->user(), $salted_pw, $new_salt);
      $this->db->log($this->access->user(), "Changed user password", 'change_password', 0);
    }

    return $rv;
  }
}
?>
