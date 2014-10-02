<?php

class LoginPage implements Page
{
  private $db;
  private $access;

  function __construct($db, $access)
  {
    $this->db     = $db;
    $this->access = $access;
  }

  public function display()
  {
    $rv = array();
    $rv['title'] = "Login";
    $rv['page']  = "login";
    $rv['data']  = array();

    if ($_SERVER['REQUEST_METHOD'] == "POST")
    {
      $rv['data']['m'] = "post";
      if (!isset($_POST["lg_user"]) || empty($_POST["lg_user"]) ||
          !isset($_POST["lg_pass"]) || empty($_POST["lg_pass"]))
      {
        return "Du musst sowohl deinen Wikipedia-Benutzernamen als auch dein Passwort angeben!";
      }
      else
      {
        $login_result = $this->access->login($_POST["lg_user"], $_POST["lg_pass"]);
        if (!$login_result)
        {
          return "Du konntest nicht angemeldet werden.";
        }
        $rv['data']['user'] = $this->access->user();
        $rv['heading'] = 'Erfolgreich angemeldet';
      }
    }
    else
    {
      $rv['data']['m'] = "get";
      $rv['heading'] = 'Anmelden';
    }

    return $rv;
  }
}