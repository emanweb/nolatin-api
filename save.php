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


  // Check if the email already exists in the table
  // todo: is this checking friendly name or email in the db ?
  $sql_check = "SELECT emailaddress FROM nolatin_exports WHERE friendly_name = ?";
  $stmt = $conn->prepare($sql_check);
  $stmt->bind_param("s", $friendly_name);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
      // Email exists, fetch the existing record
      $existing_email = $result->fetch_assoc();
      // here I guess it will be handled in the front end to use the update.php
      // $json_data = "Friendly name already exists. Would you like to update?";
      if ($existing_email['emailaddress'] == $emailaddress) {
          $json_data = "Friendly name already exists. Would you like to update?";
      } else {
          $json_data = "Friendly name already exists.";
      }
  }
    else { //friendly name doesn't exist. Add it.
    $sql_insert = "INSERT INTO nolatin_exports (friendly_name, json_content, emailaddress) VALUES (?,?,?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("sss", $friendly_name, $json_content, $emailaddress);
    $stmt_insert->execute();
    $json_data = "Your link was created successfuly";
  }

  echo json_encode($json_data);
  // Close the database connection
  $stmt->close();
  $conn->close();
}

