<?php

class IndexPage implements Page
{
  public function display()
  {
    $rv = array();
    $rv['page']    = "index";
    $rv['data']    = array();
    $rv['title']   = "Mentorenprogramm auf dem Wikimedia-Toolserver";
    $rv['heading'] = 'Überblick über die Mentorendatenbank';
    return $rv;
  }
}
