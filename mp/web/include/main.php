<?php
/*
 * main.php
 * Copyright (C) 2010 by Robin Krahl, Merlissimo and others
 *
 * This file is published under the terms of the MIT license
 * (http://www.opensource.org/licenses/mit-license.php) and the
 * LGPL (http://www.gnu.org/licenses/lgpl.html).
 *
 * For more information, see http://toolserver.org/~dewpmp.
 */

require_once('database.php');
require_once('output.php');
require_once('access.php');
require_once('validator.php');
require_once('pages/page.php');
require_once('pages/about.php');
require_once('pages/addcom.php');
require_once('pages/changepw.php');
require_once('pages/compdat.php');
require_once('pages/delcom.php');
require_once('pages/delete_mm.php');
require_once('pages/edit.php');
require_once('pages/editmm.php');
require_once('pages/error.php');
require_once('pages/index.php');
require_once('pages/info.php');
require_once('pages/log.php');
require_once('pages/login.php');
require_once('pages/logout.php');
require_once('pages/maintenance.php');
require_once('pages/maintenance_ex_mentor.php');
require_once('pages/maintenance_mm_history.php');
require_once('pages/maintenance_wm.php');
require_once('pages/mentees.php');
require_once('pages/mentorlist.php');
require_once('pages/search.php');
require_once('pages/stat.php');
require_once('pages/viewmentor.php');
require_once('pages/viewm.php');
// external code
require_once('libchart/classes/libchart.php');

/**
 * Format a date stamp.
 * @param int $d the date to format
 * @param string $def the default value if $d = 0
 * @returns string the formatted date $d in ‘d.m.Y’ or $def if $d = 0
 */
function fd($d, $def = '-')
{
  if ($d == 0)
  {
    return $def;
  }
  $date = new DateTime($d);
  return $date->format('d.m.Y');
}

/**
 * Format a date-and-time stamp.
 * @param int $dt the date and time to format
 * @param string $def the default value if $dt = 0
 * @returns string the formatted date-time $dt in ‘d.m.Y, G:s’ form or $def if
 *                 $d = 0
 */
function fdt($dt, $def = '-')
{
  if ($dt == 0)
  {
    return $def;
  }
  $date = new DateTime($dt);
  return $date->format('d.m.Y, H:i');
}

/**
 * This class is – as the name states – the interface’s main class. It co-
 * ordinates the different sub-classes.
 */
class Main
{
  /**
   * A database handle.
   */
  private $db;
  /**
   * The existing pages in form ‘access-name’ → ‘instance’. A page can be
   * called with ?action=access-name.
   */
  private $pages = array();
  /**
   * The object handling the output.
   */
  private $output;
  /**
   * The object providing login and acces group functionality.
   */
  private $access;

  /**
   * Constructor. Initialises the databases, other objects, loads the existing
   * pages.
   */
  public function __construct()
  {
    # initialise databases
    $this->db = new Database();

    $this->access = new Access($this->db);
    $this->output = new Output($this->access);

    # load pages
    $this->pages['']            = new InfoPage    ();
    $this->pages['about']       = new AboutPage   ();
    $this->pages['addcom']      = new AddComPage  ($this->db, $this->access);
    $this->pages['changepw']    = new ChangePWPage($this->db, $this->access);
    $this->pages['compdat']     = new CompDatPage ($this->db);
    $this->pages['delcom']      = new DelComPage  ($this->db, $this->access);
    $this->pages['delete_mm']   = new DeleteMmPage  ($this->db, $this->access);
    $this->pages['edit']        = new EditPage    ($this->db, $this->access);
    $this->pages['editmm']      = new EditMMPage  ($this->db, $this->access);
    $this->pages['error']       = new ErrorPage   ($this->db);
    $this->pages['index']       = new IndexPage   ();
    $this->pages['mentorlist']  = new MentorListPage($this->db);
    $this->pages['log']         = new LogPage     ($this->db);
    $this->pages['mentees']     = new MenteesPage ($this->db);
    $this->pages['maintenance'] = new MaintenancePage ($this->db);
    $this->pages['maintenance_ex_mentor'] = new MaintenanceExMentorPage ($this->db);
    $this->pages['maintenance_mm_history'] = new MaintenanceMmHistoryPage ($this->db);
    $this->pages['maintenance_wm'] = new MaintenanceWmPage ($this->db);
    $this->pages['viewmentor']  = new ViewMentorPage($this->db);
    $this->pages['viewmentee']  = new ViewMPage   ($this->db);
    $this->pages['search']      = new SearchPage  ($this->db);
    $this->pages['stat']        = new StatPage    ($this->db);
    $this->pages['login']       = new LoginPage   ($this->db, $this->access);
    $this->pages['logout']      = new LogoutPage  ($this->access);
  }

  /**
   * Does the main action: Figures out which page to load, tries to load it,
   * agregates data etc.
   */
  public function run()
  {
    $pagename = '';
    if (isset($_GET['action']))
      $pagename = trim($_GET['action']);

    if (!isset($this->pages[$pagename]))
      $pagename = 'error';

    $page = $this->pages[$pagename];
    $tmp  = $page->display();
    $cd   = array_merge($this->db->getCountsDB(), $this->db->getCountsWP());

    if (gettype($tmp) == 'string')
    {
      $this->output->critical($tmp, $cd);
    }

    $this->output->heading = $tmp['heading'];
    $this->output->title   = $tmp['title'];
    $this->output->page    = $tmp['page'];
    $this->output->data    = array_merge($this->output->data, $tmp['data'], $cd);
    $this->output->printOutput();
  }
}
