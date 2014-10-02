<?php
  /**
   * Archive a mentee.
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

function archive_mentee($db, $menteeid)
{
  # update mentee_mentor
  $mms = $db->get_mm_items_by_mentee_id($menteeid);
  foreach ($mms as $mm)
  {
    $db->archive_mm_item($mm['mm_mentor_id'],
                        $mm['mm_mentee_id'],
                        $mm['mm_start'],
                        $mm['mm_stop']);
  }
  # update mentee
  $db->archive_mentee($menteeid);
}

require('../web/include/database.php');

header('Content-type: text/xml');

$doc = new DOMDocument();
$doc->formatOutput = true;

$res = $doc->createElement('result');
$doc->appendChild($res);

if (!isset($_POST['token']) || !authenticate($_POST['token']))
{
  print_error($doc, $res, 4, 'You specified no or a wrong token.');
  echo($doc->saveXML());
  exit;
}

# all necessary params given?
if (isset($_GET['menteeid']))
{
  $menteeid = $_GET['menteeid'];
  $db = new Database();
  # exists mentee?
  $mentee = $db->getMenteeById($menteeid);
  if ($mentee == array())
  {
    print_error($doc, $res, 2, 'The specified mentee does not exist.');
  }
  else
  {
    # mentee already archived?
    if ($mentee['mentee_out'] != 0)
    {
      print_error($doc, $res, 3, 'The specified mentee is already archived.');
    }
    else
    {
      archive_mentee($db, $menteeid);
      $nm = $db->getMenteeById($menteeid);
      if ($nm['mentee_out'] != 0)
      {
        $suc = $doc->createElement('success');
        $res->appendChild($suc);

	$this->db->log('api', "Archived mentee {$mentee['mentee_user_name']}", 'archive_mentee', $menteeid);
      }
      else
      {
        print_error($doc, $res, 9, 'An unknown error occured.');
      }
    }
  }
}
else
{
  print_error($doc, $res, 1, 'You did not specify a mentee id.');
}

echo($doc->saveXML());
?>
