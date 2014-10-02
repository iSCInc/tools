<?php
/*
 * errorpage.php
 * Copyright (C) 2010 by Robin Krahl, Merlissimo and others
 *
 * This file is published under the terms of the MIT license
 * (http://www.opensource.org/licenses/mit-license.php) and the
 * LGPL (http://www.gnu.org/licenses/lgpl.html).
 *
 * For more information, see http://toolserver.org/~dewpmp.
 */

/**
 * This page is called if there occured an error loading a page.
 */
class ErrorPage implements Page
{
  /**
   * Aggregates data for displaying this page.
   * @returns string the error message.
   */
  public function display()
  {
    return "Die Seite <tt>" . trim($_GET["action"]) . "</tt> existiert nicht.";
  }
}
