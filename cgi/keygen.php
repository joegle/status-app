<?php
/*
 *
 * Visit this script to generate a password hash using BCRYPT for config.php PASSWORD_HASH definition
 *
*/

echo '<form method="post" action="' . $_SERVER['SCRIPT_NAME'] . '" name="loginform">';
echo '<h3>Create new PIN hash</h3>';
echo '<p>The default app requires a PIN consisting of numbers only.</p>';
echo '<p>Hashing done with PHP 5.5 PASSWORD_BCRYPT.</p>';

echo '<label for="login_input_username">Cost</label> ';
echo '<input id="cost" size="3" type="text" name="cost" required value="12" /> <br>';

echo '<label for="login_input_username">PIN</label> ';
echo '<input id="pin" type="password" name="pin" required /> ';
echo '<label for="pin_repeat">PIN confirm </label> ';
echo '<input id="pin_repeat" type="password" name="pin_repeat" required /> ';
echo '<input type="submit"  name="login" value="Generate hash" />';
echo '</form>';


// Check passwords match and generate a hash

if ( isset($_POST["pin"], $_POST["pin_repeat"]) 
     && !empty($_POST["pin"]) 
     && $_POST["pin"] == $_POST["pin_repeat"] ) {

  // Time the hashing speed 
  $crypt_start = microtime(true);
  $hash = password_hash($_POST["pin"], PASSWORD_BCRYPT,["cost" => $_POST["cost"]]); 
  $crypt_end = microtime(true);

  if ( $hash ) { 

    echo "<p>Copy this hash into your <i>config.php</i> file:</p>";
    echo "<blockquote>" . $hash . "</blockquote>";

    echo "<p>Took ".((float)$crypt_end-$crypt_start)." seconds to complete.</p>";
  }
  else
    echo "Hashing error";

  
  echo "</blockquote>";


}
