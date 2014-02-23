<?php
//phpinfo();
//error_reporting(E_ALL);
ini_set('display_errors', 1);


echo "<h2>install.php</h2>";
echo "<p>Consult <a href='../README.md'>README.md</a> for install instructions.</p>";


// Look for config.php and load it

echo "<h3>config.php</h3>";
if ( ! file_exists("./config.php")){
  echo "<p>No config.php file found in cgi/</p>";
  echo "<p>Fill out config-sample.php and copy it as config.php</p>";
  echo "<p>Aborting database setup</p>";
  
  die();
  
}
else{
  include "./config.php";
  echo "<p>Loading config.php... Success  </p>";

}


// Database setup 

echo "<h3>Database setup</h3>";
$db=null;

$db = @new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

if ( $db->connect_errno ) {
  echo "<p>Failed to connect to MySQL: (" . $db->connect_errno . ") " . $db->connect_error. "</p>";
  die();
}
else {

  $table_name = TABLE_PREFIX."status";
  
  // Check if table exists already
  $result = mysqli_query($db,"show tables like '${table_name}'");

  if ( $result->num_rows ) {

      echo "<p>There already exists a table '<i>${table_name}</i>'</p>";
      echo "<p>If the table '<i>${table_name}</i>' was created using this script then the database setup is complete, otherwise you can change the TABLE_PREFIX definition in <i>config.php</i> to prevent table name conflicts.</p>";

    }
  else {

    $query_str = "CREATE TABLE IF NOT EXISTS ${table_name} (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `message` text COLLATE latin1_german2_ci,
  `date` bigint(11) DEFAULT NULL,
  `lat` double DEFAULT NULL,
  `lon` double DEFAULT NULL,
  `alt` double DEFAULT NULL,
  `ip` varchar(100) COLLATE latin1_german2_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_german2_ci;";

  $stmt = $db->prepare($query_str);

  echo "<p>Creating table with MySQL:</p><pre>" . $query_str . "</pre>";


  if ( ! $stmt->execute() ) {
    echo "Table creation failed: (" . $db->errno . ") " . $db->error;
    echo "<p>Check MySQL settings in <i>config.php</i></p>";
  }
  else {
    echo "<p>Created table <b>" . $table_name . "</b></p>";
  }

  mysqli_close($db);
  }
}


// Last steps

echo "<h3>Security steps</h3>";
echo "<p>Change the default password by generating a new PIN password hash at <a href='keygen.php'>keygen.php</a> to place in config.php</p>";
echo "<p>Disable read permisions of this script to prevent the public from executing it.</p>";
echo "<p><a href='../index.html'>Test your install</a>, default password: '123' (set TESTING flag to true to see debug messages in update-status.php JSON responses)</p>";