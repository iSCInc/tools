<?php
/*
 * aboutpage.php
 * Copyright (C) 2010 by Robin Krahl, Merlissimo and others
 *
 * This file is published under the terms of the MIT license
 * (http://www.opensource.org/licenses/mit-license.php) and the
 * LGPL (http://www.gnu.org/licenses/lgpl.html).
 *
 * For more information, see http://toolserver.org/~dewpmp.
 */

/**
 * This page contains information about the implementation, the license etc.
 */
class AboutPage implements Page
{
  /**
   * Aggregates data for displaying this page.
   * @returns array data about the page to display
   */
  public function display()
  {
    $rv = array();
    $rv['page']    = "about";
    $rv['data']    = array();
    $rv['title']   = "Über das Mentorenprogramm auf dem Wikimedia-Toolserver";
    $rv['heading'] = "Über die Mentorendatenbank";
    return $rv;
  }
}
