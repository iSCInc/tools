<?php

class LogoutPage implements Page
{
  private $access;

  function __construct($access)
  {
    $this->access = $access;
  }

  public function display()
  {
    $this->access->logout();

    $rv = array();
    $rv['heading'] = 'Erfolgreich abgemeldet';
    $rv['title']   = "Erfolgreich abgemeldet";
    $rv['page']    = "logout";
    $rv['data']    = array();
    return $rv;
  }
}