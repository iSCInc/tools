<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

include('../web/include/database.php');

function now($format)
{
  $dt = new DateTime();
  return $dt->format($format);
}

echo "Updating mentor entries…\n";
echo "Running at " . now('Y-m-d H:i:s') . " @ " . `uname -n` . "\n";

$db = new Database();

echo "Checking renames…\n";
$rms = $db->get_renamed_mentors();
echo "  Found " . count($rms) . " renamed mentors.\n";
foreach ($rms as $m)
{
  echo "  Renaming mentor with user id " . $m['mentor_user_id'] . "\n";
  echo "    Previously: " . $m['mentor_user_name'] . "\n";
  echo "    Now:        " . $m['user_name']        . "\n";
  $db->rename_mentor($m['mentor_id'], $m['user_name']);
  echo "    Done.\n";

  $db->log('maintenance', "Renaming mentor with user id {$m['mentor_user_id']}", "rename_mentor", 0);
}

echo "\n";

$mentor_cat = "Benutzer_ist_Mentor";

echo "Searching mentors who are not in their category…\n";

$old_ms = $db->get_archived_mentors($mentor_cat);
echo "  Found " . count($old_ms) . " archived mentors.\n";
foreach ($old_ms as $m)
{
  echo "    Dropping mentor " . $m['mentor_user_name'] . "\n";
  $db->archive_mentor($m['mentor_id']);
  echo "    Done.\n";
  $db->log('maintenance', "Dropping mentor {$m['mentor_user_name']}", 'drop_mentor', 0);
}

echo "\n";


echo "Searching mentors who are in the category but not in the database…\n";

$new_ms = $db->get_new_mentors($mentor_cat);
echo "  Found " . count($new_ms) . " new mentors.\n";
foreach ($new_ms as $m)
{
  echo "    Adding mentor " . $m['mentor_name'] . "\n";
  $db->add_mentor($m['user_id'], $m['mentor_name']);
  echo "    Done.\n";
  $db->log('maintenance', "Adding mentor {$m['mentor_name']}", 'add_mentor', 0);
}

echo "\n";

echo "Finished!\n";