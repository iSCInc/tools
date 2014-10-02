<?php
/*
 * output.php
 * Copyright (C) 2010 by Robin Krahl, Merlissimo and others
 *
 * This file is published under the terms of the MIT license
 * (http://www.opensource.org/licenses/mit-license.php) and the
 * LGPL (http://www.gnu.org/licenses/lgpl.html).
 *
 * For more information, see http://toolserver.org/~dewpmp.
 */

/**
 * This class handles the output. It loads the template pages, provides the
 * data aggregated elsewhere and prints the output. It also provides an
 * function for displaying critical errors.
 */
class Output
{
  /**
   * An instance of the Access class providing information about login and
   * user groups.
   */
  private $access;
  /**
   * The current page’s title.
   */
  public $title;
  /**
   * The current page’s h1 heading.
   */
  public $heading;
  /**
   * The data needed for displaying the page and used by the template file.
   */
  public $data;
  /**
   * The name of the current page’s include file (without ‘.html’)
   */
  public $page;
  /**
   * Additional things placed in the head tag.
   */
  public $header;

  /**
   * Constructor. Initialises the variables.
   * @param Access $access object with access information
   */
  function __construct(&$access)
  {
    $this->access = $access;

    $this->title   = "Mentorenprogramm auf dem Wikimedia-Toolserver";
    $this->heading = "Mentorenprogramm";
    $this->data    = array();
    $this->page    = "";
    $this->header  = "";
  }

  /**
   * Displays an critical error and prints the output.
   * @param string $msg  the error message
   * @param array  $data data that may be used by the error page
   */
  public function critical($msg, $data)
  {
    $this->title   = "Fehler";
    $this->heading = "Fehler";
    $this->data    = $data;
    $this->data['error'] = $msg;
    $this->page    = "error";
    $this->printOutput();
  }

  /**
   * Prints the output by loading certain templates. After printing the output,
   * it quits the PHP parser.
   */
  public function printOutput()
  {
    if(file_exists(__DIR__ . "/../templates/" . $this->page . ".header")){
    	$this->header = file_get_contents(__DIR__ . "/../templates/" . $this->page . ".header");
    }
    ob_start("ob_gzhandler");
    include(__DIR__ . "/../templates/header.html");
    include(__DIR__ . "/../templates/" . $this->page . ".html");
    include(__DIR__ . "/../templates/footer.html");
    ob_end_flush();
    exit;
  }
}
