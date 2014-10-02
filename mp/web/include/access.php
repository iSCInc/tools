<?php
/*
 * access.php
 * Copyright (C) 2010 by Robin Krahl, Merlissimo and others
 *
 * This file is published under the terms of the MIT license
 * (http://www.opensource.org/licenses/mit-license.php) and the
 * LGPL (http://www.gnu.org/licenses/lgpl.html).
 *
 * For more information, see http://toolserver.org/~dewpmp.
 */

/**
 * This class provides functionality to
 *   - log-in users and
 *   - manage several access groups.
 */
class Access
{
  /**
   * Indicates if the user is currently logged in.
   */
  private $logged_in;
  /**
   * If the current user is logged in, this variable holds his user name.
   * Otherwise, it’s an empty string.
   */
  private $user;
  /**
   * Holds an instance of the Database object.
   */
  private $db;


  /**
   * Constructor. Checks the session variables and initialises the private
   * variables.
   * @param Database $db an instance of the Database object
   */
  public function __construct($db)
  {
    # initialise
    $this->db        = $db;
    $this->logged_in = false;
    $this->user      = "";

    # check session
    session_start();
    if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && isset($_SESSION['user']))
    {
      $this->logged_in = true;
      $this->user      = trim($_SESSION['user']);
    }
  }

  /**
   * Checks if the current user is logged in.
   * @returns bool ‘true’ if the user is currently logged in
   */
  public function logged_in()
  {
    return $this->logged_in;
  }

  /**
   * Checks if the current user is allowed to change the database. In the
   * moment, it’s just checking if the user’s logged in. In the future, we may
   * implement different user groups.
   * @returns bool ‘true’ if the current user is allowed to change database
   *               entries
   */
  public function is_editor()
  {
    return $this->logged_in();
  }

  /**
   * Checks who’s currently logged in.
   * @returns bool the current user’s name or an empty string if the user is 
   *               not logged in.
   */
  public function user()
  {
    return $this->user;
  }

  /**
   * Tries to log-in a user and checks if the given password’s SHA1
   * correspondents to the stored password hash of this user.
   * @param string $user     the user name
   * @param string $password the password
   * @returns bool ‘true’ if the log-in was successfull
   */
  public function login($user, $password)
  {
#echo "<p>salt" . $this->generate_salt() . "</p>";
#die();
    $db_hash_salt_result = $this->db->get_hash_and_salt_for_user($user);
    if ($db_hash_salt_result == -1 || $password == NULL || strlen($password) == 0)
    {
      return false;
    }
    $db_hash_with_salt = $db_hash_salt_result['mentor_login_password'];
    $db_salt = $db_hash_salt_result['mentor_pw_salt'];
    $given_pw_hash_with_salt = $this->doubleSaltedHash($password, $db_salt);
#echo $given_pw_hash_with_salt;
#die();
    if ($db_hash_with_salt != $given_pw_hash_with_salt)
    {
      return false;
    }
    $_SESSION['logged_in'] = true;
    $_SESSION['user']      = $user;
    $this->logged_in       = true;
    $this->user            = $user;

    return true;
  }
 
  public function generate_salt() {
        $dummy = array_merge(range('0', '9'));
        mt_srand((double)microtime()*1000000);
        for ($i = 1; $i <= (count($dummy)*2); $i++)
        {
                $swap = mt_rand(0,count($dummy)-1);
                $tmp = $dummy[$swap];
                $dummy[$swap] = $dummy[0];
                $dummy[0] = $tmp;
        }
        return sha1(substr(implode('',$dummy),0,9));
  }

  public function doubleSaltedHash($pw, $salt)
  {
    return sha1($salt.sha1($salt.sha1($pw)));
  }

  /**
   * Logs-out the current users, destroys the session variables and resets the
   * current user. Changes nothing if the current user is not logged in.
   */
  public function logout()
  {
    session_destroy();
    $this->user      = "";
    $this->logged_in = false;
  }
}
?>
