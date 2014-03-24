<?php
/*!
 * status-update.php
 * Copyright 2013 Joseph Wright @joegle.com
 * Licensed under The MIT License (MIT) 
 * @link https://github.com/joegle/status-app/
 * 
 * A php application for a single user with 'registration,' login, logout, cookie sessions.
 * Uses password hashing with the PHP 5.5 password hashing functions. 
 * Modeled after https://github.com/panique/php-login-one-file
 * 
 */

//error_reporting(E_ERROR | E_PARSE); //E_ALL
//error_reporting(E_ALL); //
//ini_set('display_errors', 1);

define('CONFIG_FILE','./cosnfig.php');

if ( file_exists(CONFIG_FILE) ){
  require(CONFIG_FILE);
}


/*
 * SingleUserSession initiates, authenticates, and operates database payloads for 
 * a single user with the HTTP POST requests parameters: 
 *
 *     password - password to be hash verified with THE_KEY defined in cgi/congif.php 
 *     cookie - sessionid provided cookie from an authenicated session
 *     message - message to store in database
 *     lat , lon , alt - latitude, longitude, altitude floats for message tagging in database
 *     date - optionally defined epoch timestamp for message
 *
 * Configuration file: config.php
 * Install script: install.php 
 *
 */
class SingleUserSession {
  
  private $db_connection = null;
  
  private $logged_in = false;
  
  private $session_window = SESSION_TIMEOUT; //seconds
  
  
  public $response = array('success'=> 0,   // Status was written to database
			   'validated' => 0,    // User needs to log in
			   'error' => 0,    // Some failure
			   'messages' => "",   
			   'log' => array()
			   );
  
  
  public function __construct()  {
    if ( $this->requirementsCheck() ) {

      session_start();
      //session_regenerate_id()
 
      $this->runApplication();
    }
    else {
      
    }
  }
  
  
  private function requirementsCheck() {
    $requirements = true;
    
    if ( ! extension_loaded("mysqli") ) {
      $this->log("mysqli module not loaded");
      $requirements = false;
    }
    
    if ( version_compare(PHP_VERSION, '5.5.0', '<') ) {
      $this->log("PHP 5.5.0 is required");
      $requirements = false;
    } 
    elseif ( version_compare(PHP_VERSION, '5.5.0', '>=') ) {
      
    }

    if ( ! file_exists(CONFIG_FILE) ){
      $this->log("No config.php file at defined location in " . basename(__FILE__));
      $requirements = false;     
    }

    
    if ( ! $requirements ) {
      $this->response['message'] = "Requirement failure, check install";
      $this->response['error'] = 1;
      $this->sendResponse();
    }

    return $requirements;
  }
  
  /*
   * Do all the session specific login
   * This is were session behavior is defined 
   *
   */
  // 
  public function runApplication() {
    
    // Look for a password and check it then start a cookie verified session
    if ( ! empty( $_POST["password"] ) ) {
      $this->log("Password supplied");
      
      if ( $this->verifyPassword() ){
	$this->startCookieSession();
      }
      
    } 
    
    elseif ( isset($_COOKIE['PHPSESSID']) ) {
      $this->log("Cookie Detected");
      
      $this->verifySession();
      
    }
    
    // User is now verified...
    if ( $this->getUserLoginStatus() ) {

      $this->response['validated'] = 1;

      // Log out option
      if ( isset($_GET["action"]) && $_GET["action"] == "logout" ) {
	$this->doLogout();
	$this->response['end_session'] = 1;
	$this->response['message'] = "Logged out";
      }
      else{


	// Submit the post data to database
	if ( $this->createDatabaseConnection() && $this->insertStatement() ) {
	  $this->closeDatabaseConnection();
	  $this->response['success'] = 1;
	}
	else{
	  $this->response['error'] = 1;
	  $this->response['message'] = "Database error";
	}
      }
      
      
      if ( ! $this->verifySessionTime() ) {
	$this->doLogout();
      }
      
    }
    else{
      // User was not verified; close session
      $this->doLogout();
      $this->response['validated'] = 0;
    }
    
    // return a response 
    $this->sendResponse();
  }
  


  private function sendResponse() {
    header('Content-Type: application/json');
    echo json_encode( $this->response);
  }
  
  private function log($message){
    if ( TESTING ) {

      array_push($this->response['log'], "PHP: ".$message );
      //$this->response['log'] .= "PHP: ".$message."<br>\n";
    }
  }
  

  public function getUserLoginStatus() {
    return $this->logged_in;
  }


  /*
   *  Session primitives
   *
   */

  private function verifyPassword() {
    if ( password_verify($_POST["password"], PASSWORD_HASH )){

      $this->log("Successfully validated");
      $this->log("Password correct");
      
      $this->logged_in = true;
      
      return true;
    }else{
      $this->log("Password incorrect");
    }
    
    return false;
  }

  private function startCookieSession() {
    if ( $this->getUserLoginStatus() ) {

      $session_hash = password_hash(session_id(), PASSWORD_BCRYPT,["cost" => BCRYPT_COST]);

      if ( $session_hash ) {
	$_SESSION['hash'] = $session_hash;
	$_SESSION['start_time'] = time();
	$this->log("Session started for time");
	return true;
      }

    }
    return false;
  }

  private function doLogout() {
    $_SESSION = array();
    session_destroy();
    $this->logged_in = false;
    $this->log("Logged out, session closed");
  }
  
  
  private function verifySessionTime() {
    if(isset($_SESSION["start_time"]) && time() - $_SESSION["start_time"] <= $this->session_window) {
      
      $this->log("Inside session window");
      return true;
    }
    else{
      $this->log("Session window expired");
    }
    
    return false;
  }
  
  private function verifyCookie() {
    if (isset($_COOKIE["PHPSESSID"],$_SESSION['hash']) && 
	password_verify($_COOKIE["PHPSESSID"], $_SESSION['hash'])){
      $this->log("Good COOKIE");
      return true;
    }else{
      $this->log("BAD COOKIE");
    }
    
    return false;
    
  }
  
  private function verifySession(){
    if( $this->verifySessionTime() && $this->verifyCookie() ){
      $this->logged_in = true;      
      
    }else{
      return false;
    }
    
    return true;
  }
  
  /*
   * Database methods 
   *
   */
  
  private function closeDatabaseConnection() {
    mysqli_close($this->db_connection);
  }
  
  private function createDatabaseConnection() {

    $this->db_connection = @new mysqli(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);

    if ($this->db_connection->connect_errno) {
      $this->log("Failed to connect to MySQL: (" . $this->db_connection->connect_errno . ") " .$this->db_connection->connect_error);
      return false;
    }

    return true;
  }
  
  private function insertStatement() {

    // Replace empty coordinate fields with null values
    $altitude = empty($_POST['alt']) ? null : $_POST['alt'];
    $latitude = empty($_POST['lat']) ? null : $_POST['lat'];
    $longitude = empty($_POST['lon']) ? null : $_POST['lon'];

    $date_fallback = empty($_SERVER["REQUEST_TIME"]) ? time() : $_SERVER["REQUEST_TIME"];

    $date = empty($_POST['date']) ? $date_fallback : $_POST['date'];


    $status_table = TABLE_PREFIX."status";
    $insert_stmt = $this->db_connection->prepare("INSERT INTO "
						 . $status_table
						 . " (message,ip,date,lon,lat,alt) VALUES (?,?,?,?,?,?)");

    $insert_stmt->bind_param("ssiddd",
			     $_POST["message"], 
			     $_SERVER['REMOTE_ADDR'], 
			     $date,
			     $longitude,
			     $latitude,
			     $altitude  );
    $insert_stmt->execute();

    $rows_inserted = $insert_stmt->affected_rows;

    if ( $rows_inserted ) {
      $this->log($rows_inserted." rows inserted");
      return true;
    }
    else{
      $this->log("Insert error, 0 rows inserted");
      return false;
    }
    
  }
  
  
}


// invoke 
$application = new SingleUserSession();
