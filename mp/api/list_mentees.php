<?php
  /**
   * Print a list of all mentees.
   */
   
error_reporting(E_ALL);
ini_set('display_errors', '1');

require('../web/include/database.php');

$db = new Database();
$mentees = $db->get_all_mentees();

header('Content-type: text/xml');

$doc = new DOMDocument();
$doc->formatOutput = true;

$ml = $doc->createElement('menteelist');
$doc->appendChild($ml);
foreach ($mentees as $m)
{
  $mentee = $doc->createElement('mentee');
  
  $mname = $doc->createElement('name');
  $mname->appendChild($doc->createTextNode($m['mentee_user_name']));
  $mentee->appendChild($mname);
  
  $mid = $doc->createElement('id');
  $mid->appendChild($doc->createTextNode($m['mentee_id']));
  $mentee->appendChild($mid);

  $min = $doc->createElement('in');
  $min->appendChild($doc->createTextNode($m['mentee_in']));
  $mentee->appendChild($min);

  if ($m['mentee_out'] != '')
  {
    $mout = $doc->createElement('out');
    $mout->appendChild($doc->createTextNode($m['mentee_out']));
    $mentee->appendChild($mout);
  }

  $mentor_id = $doc->createElement('mentor_id');
  $mentor_id->appendChild($doc->createTextNode($m['mentor_id']));
  $mentee->appendChild($mentor_id);
  
  $ml->appendChild($mentee);
}
echo($doc->saveXML());
?>