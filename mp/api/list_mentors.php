<?php
  /**
   * Print a list of all mentors.
   */
   
error_reporting(E_ALL);
ini_set('display_errors', '1');

require('../web/include/database.php');

$db = new Database();
$mentors = $db->get_all_mentors();

header('Content-type: text/xml');

$doc = new DOMDocument();
$doc->formatOutput = true;

$ml = $doc->createElement('mentorlist');
$doc->appendChild($ml);
foreach ($mentors as $m)
{
  $mentor = $doc->createElement('mentor');
  $mname = $doc->createElement('name');
  $mname->appendChild($doc->createTextNode($m['mentor_user_name']));
  $mentor->appendChild($mname);
  $mid = $doc->createElement('id');
  $mid->appendChild($doc->createTextNode($m['mentor_id']));
  $mentor->appendChild($mid);
  $ml->appendChild($mentor);
}

echo($doc->saveXML());
?>