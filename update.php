<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');
// list of allowed IP addresses
$allowed_ips = array('');

// get the user's IP address
$user_ip = $_SERVER['REMOTE_ADDR'];

// check if the user's IP is in the list of allowed IPs
// if (!in_array($user_ip, $allowed_ips)) {
//     // if the user's IP is not in the list of allowed IPs, show an error message and exit
//     die("Access denied. Your IP address ($user_ip) is not allowed to access this page.");
// }

if (!isset($_POST['localform'])){
  // if the form was submitted via JSON
  $rest_json = file_get_contents("php://input");
  $_POST = json_decode($rest_json, true);
}

// Check if the form was submitted via POST

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
  // $friendly_name = mysqli_real_escape_string($conn, $friendly_name);
  // $json_content = mysqli_real_escape_string($conn, $json_content);
  // $emailaddress = mysqli_real_escape_string($conn, $emailaddress);
  
  // Check and post to backups table
    // Get backup list for friendly name
    $backups_sql = "SELECT version FROM nolatin_backups WHERE friendly_name = ?";
    $backups_stmt = $conn->prepare($backups_sql);
    $backups_stmt->bind_param("s", $friendly_name);
    $backups_stmt->execute();
    $backups_result = $backups_stmt->get_result();

    if ($backups_result->num_rows == 0) {
      // If none exist, create v0 & v1
        // Get current version
        $current_sql = "SELECT json_content FROM nolatin_exports WHERE friendly_name = ?";
        $current_stmt = $conn->prepare($current_sql);
        $current_stmt->bind_param("s", $friendly_name);
        $current_stmt->execute();
        $current_result = $current_stmt->get_result();

        $current_json = $current_result->fetch_all(MYSQLI_ASSOC);

        // Post v0
        $v0 = 0
        $inactive = 'inactive'
        $post_v0_sql = "INSERT INTO nolatin_backups (friendly_name, json_content, version, status) VALUES (?,?,?,?)";
        $post_v0_stmt = $conn->prepare($post_v0_sql);
        $post_v0_stmt->bind_param("ssis", $friendly_name, $current_json, $v0, $inactive);
        $post_v0_stmt->execute();

        // Post v1
        $v1 = 1
        $post_v1_sql = "INSERT INTO nolatin_backups (friendly_name, json_content, version) VALUES (?,?,?)";
        $post_v1_stmt = $conn->prepare($post_v1_sql);
        $post_v1_stmt->bind_param("ssi", $friendly_name, $json_content, $v1);
        $post_v1_stmt->execute();
    } else {
      // If backups do exist, create new version & set other versions to inactive
      $update_old_sql = "UPDATE nolatin_backups SET status = 'inactive' WHERE friendly_name = ?";
      $update_old_stmt = $conn->prepare($update_old_sql);
      $update_old_stmt->bind_param("s", $friendly_name);
      $update_old_stmt->execute();

      $version_num = $backups_result->num_rows;

      $post_new_sql = "INSERT INTO nolatin_backups (friendly_name, json_content, version) VALUES (?,?,?)";
      $post_new_stmt = $conn->prepare($update_old_sql);
      $post_new_stmt->bind_param("ssi", $friendly_name, $json_content, $version_num);
      $post_new_stmt->execute();
    }
    
  // Update original
  $update_original_sql = "UPDATE nolatin_exports SET json_content = ? WHERE friendly_name = ?";
  $update_original_stmt = $conn->prepare($update_original_sql);
  $update_original_stmt->bind_param("ss", $json_content, $friendly_name);
  $update_original_stmt->execute();

  // Close the database connection
  mysqli_close($conn);
}
?>
