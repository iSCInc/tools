<?php
/*
 * searchpage.php
 */

class SearchPage implements Page
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function display()
  {
    $rv = array();
    $rv['heading'] = 'Suche';
    $rv['title']   = "Suche";
    $rv['page']    = "search";
    $rv['data']['active']   = true;
    $rv['data']['inactive'] = true;
    $rv['data']['where']    = 'all';
    $rv['data']['content']  = '';
    $rv['data']['w']        = 'onlyform';

    if (isset($_GET['search-content']))
    {
      $rv['data']['w'] = 'results';
      $rv['data']['content'] = $_GET['search-content'];
      if (isset($_GET['search-where']))
      {
	$sw = $_GET['search-where'];
	if ($sw == 'all' || $sw == 'mentor' || $sw == 'mentee')
	{
	  $rv['data']['where'] = $sw;
	}
      }
      $search_active = false;
      if (isset($_GET['search-active']))
      {
	$search_active = true;
      }
      $rv['data']['active'] = $search_active;
      $search_inactive = false;
      if (isset($_GET['search-inactive']))
      {
	$search_inactive = true;
      }
      $rv['data']['inactive'] = $search_inactive;
      

      # try to get matching person
      $rv['data']['dm'] = false;             # direct match
      if ($rv['data']['where'] == 'mentor')
      {
	$m = $this->db->getMentorByNameAndActivity($rv['data']['content'], $search_active, $search_inactive);
	if (!empty($m))
	{
	  header("Location: index.php?action=view&id={$m['mentor_user_id']}");
	}
      }
      elseif ($rv['data']['where'] == 'mentee')
      {
	$m = $this->db->getMenteeByNameAndActivity($rv['data']['content'], $search_active, $search_inactive);
	if (!empty($m))
	{
	  header("Location: index.php?action=viewmentee&id={$m['mentee_user_id']}");
	}
      }
      else
      {
	$mr = $this->db->getMentorByNameAndActivity($rv['data']['content'], $search_active, $search_inactive);
	$me = $this->db->getMenteeByNameAndActivity($rv['data']['content'], $search_active, $search_inactive);

	if (!empty($mr) && empty($me))
	{
	  header("Location: index.php?action=view&id={$mr['mentor_user_id']}");
	}
	elseif (empty($mr) && !empty($me))
	{
	  header("Location: index.php?action=viewmentee&id={$me['mentee_user_id']}");
	}
	elseif (!empty($mr) && !empty($me))
	{
	  $rv['data']['sr'] = 'two_direct';
	  $rv['data']['mr'] = $mr;
	  $rv['data']['me'] = $me;
	  $rv['data']['dm'] = true;
	}
      }

      # no direct match -- search results
      if (!$rv['data']['dm'])
      {
	if ($rv['data']['where'] == 'mentor')
	{
	  $ms = $this->db->getMentorsByNameRegExpAA($rv['data']['content'], $search_active, $search_inactive);
	  $rv['data']['sr'] = 'mentors';
	  $rv['data']['result'] = $ms;
	}
	elseif ($rv['data']['where'] == 'mentee')
	{
	  $ms = $this->db->getMenteesByNameRegExpAA($rv['data']['content'], $search_active, $search_inactive);
	  $rv['data']['sr'] = 'mentees';
	  $rv['data']['result'] = $ms;
	}
	else
	{
	  $mrs = $this->db->getMentorsByNameRegExpAA($rv['data']['content'], $search_active, $search_inactive);
	  $mes = $this->db->getMenteesByNameRegExpAA($rv['data']['content'], $search_active, $search_inactive);
	  $rv['data']['sr'] = 'mentors_and_mentees';
	  $rv['data']['result'] = array();
	  $rv['data']['result']['mrs'] = $mrs;
	  $rv['data']['result']['mes'] = $mes;
	}
      }
    }

    $rv['data']['mentor_proposals'] = $this->db->getMentorNames();
    $rv['data']['mentee_proposals'] = $this->db->getMenteeNames();

    return $rv;
  }
}
?>