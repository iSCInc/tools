<?php
  /**
   * Add a mentee.
   */

error_reporting(E_ALL);
ini_set('display_errors', '1');

require('auth.php');

function print_error($doc, $res, $errno, $errdesc)
{
  $err = $doc->createElement('error');
  $res->appendChild($err);
  $err->setAttribute('errno',       $errno);
  $err->setAttribute('description', $errdesc);
}

require('../web/include/database.php');

function add_mentor_mentee_relation($db, $mentee_name, $mentor_id)
{
  $m = $db->getMenteeByName($mentee_name);
  if (isset($m['mentee_id']))
  {
    $db->add_mm_item($mentor_id, $m['mentee_id']);
    return true;
  }
  return false;
}

header('Content-type: text/xml');

$doc = new DOMDocument();
$doc->formatOutput = true;

$res = $doc->createElement('result');
$doc->appendChild($res);

if (!isset($_POST['token']) || !authenticate($_POST['token']))
{
  print_error($doc, $res, 3, 'You specified no or a wrong token.');
  echo($doc->saveXML());
  exit;
}

# all necessary params given?
if (isset($_GET['menteename']) && isset($_GET['mentorid']))
{
  $db = new Database();
  $menteename   = $_GET['menteename'];
  $mentorid     = $_GET['mentorid'];
  $menteeuserid = $db->get_user_id($menteename);
  if ($menteeuserid == -1)
  {
    print_error($doc, $res, 4, 'The user doesn’t exist.');
  }
  else
  {
    # does the mentee already exist?
    $mentee = $db->getMenteeByName($menteename);
    if ($mentee == array())
    {
      $db->add_mentee($menteename, $menteeuserid);
      if (add_mentor_mentee_relation($db, $menteename, $mentorid))
      {
	$suc = $doc->createElement('success');
	$res->appendChild($suc);

	$this->db->log('api', "Added mentee $menteename.", 'add_mentee', 0);
      }
      else
      {
	print_error($doc, $res, 9, 'An unknown error occured.');
      }
    }
    else
    {
      print_error($doc, $res, 2, 'There is already a mentee with this name.');
    }
  }
}
else
{
  print_error($doc, $res, 1, 'You did not specify a mentee name or a mentor id.');
}

echo($doc->saveXML());
?>
