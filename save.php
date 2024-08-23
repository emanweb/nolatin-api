<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
// list of allowed IP addresses
$allowed_ips = array('');

// get the user's IP address
$user_ip = $_SERVER['REMOTE_ADDR'];

// check if the user's IP is in the list of allowed IPs
//if (!in_array($user_ip, $allowed_ips)) {
  // if the user's IP is not in the list of allowed IPs, show an error message and exit
//  die("Sorry, access denied. Your IP address ($user_ip) is not allowed to access this page.");
//}

if (!isset($_POST['localform'])){
  // if the form was submitted via JSON
  $rest_json = file_get_contents("php://input");
  $_POST = json_decode($rest_json, true);
}

if ($_POST) {
  // Retrieve the form data using POST
  $friendly_name = $_POST["friendly_name"];
  $json_content = $_POST["json_content"];
  $emailaddress = $_POST["emailaddress"];

  // Validate form data (optional)
  // ...

  // Connect to MySQL database
  include ('../../wo-config.php');
  $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

  // Check if connection was successful
  if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
  }

   // Escape special characters in the form data to prevent SQL injection attacks
  $friendly_name = mysqli_real_escape_string($conn, $friendly_name);
  $json_content = mysqli_real_escape_string($conn, $json_content);
  $emailaddress = mysqli_real_escape_string($conn, $emailaddress);


  // Construct the SQL query
  $sql = "INSERT INTO nolatin_exports (friendly_name, json_content, emailaddress) VALUES ('$friendly_name', '$json_content', '$emailaddress')";

  // Execute the SQL query
try {
  if (mysqli_query($conn, $sql)) {
    $json_data = "Your link was created successfuly";
  } else {
    $json_data =  "Error Inserting data: " . $sql . " " . mysqli_error($conn) ;
  }
 } catch (mysqli_sql_exception $e) {
  if ($e->getCode() == 1062) { // 1062 is the MySQL error code for duplicate entry
    // Handle the duplicate entry error here
    $email_sql = "SELECT emailaddress FROM nolatin_exports WHERE friendly_name = '$friendly_name'";
    $email_result = mysqli_query($conn, $email_sql);
    syslog(LOG_INFO, "EMAIL RESULT FETCH");
    syslog(LOG_INFO, mysqli_fetch_assoc($email_result)[0]);
    if (mysqli_fetch_assoc($email_result)[0] == $emailaddress) {
      $json_data = "Friendly name already exists. Would you like to update?";
    } else {
      $json_data = "Friendly name already exists.";
    }
  } else {
    // Handle other MySQL errors here
    $json_data = "Error exception: " . $sql . " " . mysqli_error($conn) ;
  }
}
  echo json_encode($json_data);
  // Close the database connection
  mysqli_close($conn);
}
?>
