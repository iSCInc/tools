<?php
/*
 * page.php
 * Copyright (C) 2010 by Robin Krahl, Merlissimo and others
 *
 * This file is published under the terms of the MIT license
 * (http://www.opensource.org/licenses/mit-license.php) and the
 * LGPL (http://www.gnu.org/licenses/lgpl.html).
 *
 * For more information, see http://toolserver.org/~dewpmp.
 */

/**
 * This interface is inherited by all pages that will be visible in this
 * application.
 */
interface Page
{
  /**
   * Aggregates data for displaying this page.
   * @returns mixed data about the page to display. If the return value is a
   *                string, it’s interpreted as an error message.
   */
  public function display();
}
?>
