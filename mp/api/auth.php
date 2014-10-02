<?php
function authenticate($token)
{
  $tf_path = __DIR__ . "/../api_token.txt";
  $real_token = file_get_contents($tf_path);
  if (!empty($token) && trim($real_token) === trim($token))
  {
    return true;
  }
  return false;   
}
