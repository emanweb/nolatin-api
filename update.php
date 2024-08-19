<?php
// list of allowed IP addresses
$allowed_ips = array('184.171.244.81', '68.108.129.180', );

// get the user's IP address
$user_ip = $_SERVER['REMOTE_ADDR'];

// check if the user's IP is in the list of allowed IPs
if (!in_array($user_ip, $allowed_ips)) {
    // if the user's IP is not in the list of allowed IPs, show an error message and exit
    die("Access denied. Your IP address ($user_ip) is not allowed to access this page.");
}

// Check if the form was submitted via POST

if ($_SERVER["REQUEST_METHOD"] == "POST") {
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
  
  // Check and post to backups table
    // Get backup list for friendly name
    $backups_sql = "SELECT version FROM nolatin_backups WHERE friendly_name = '$friendly_name'";

    $backups_result = mysqli_query($conn, $backups_sql);
    $backups = array();
    while($row = mysqli_fetch_assoc($backups_result)) {
      $backups[] = $row;
    }

    if (count($backups) == 0) {
      // If none exist, create v0 & v1
        // Get current version
        $current_sql = "SELECT json_content FROM nolatin_exports WHERE friendly_name = '$friendly_name'";
        $current_result = mysqli_query($conn, $current_sql);
        //TODO: FIX JSON CONVERSION
        $current_json = mysqli_fetch_assoc($current_result)[0];
        // Post v0
        $post_v0_sql = "INSERT INTO nolatin_backups (friendly_name, json_content, version, status) VALUES ('$friendly_name', '$current_json', 0, 'inactive')";
        try {
          if (mysqli_query($conn, $post_v0_sql)) {
            echo "v0 created successfully";
          } else {
            echo "Error: " . $post_v0_sql . "<br>" . mysqli_error($conn);
          }
        } catch (mysqli_sql_exception $e) {
          echo "Error: " . $e;
        }
        // Post v1
        $post_v1_sql = "INSERT INTO nolatin_backups (friendly_name, json_content, version) VALUES ('$friendly_name', '$json_content', 1)";
        try {
          if (mysqli_query($conn, $post_v1_sql)) {
            echo "v1 created successfully";
          } else {
            echo "Error: " . $post_v1_sql . "<br>" . mysqli_error($conn);
          }
        } catch (mysqli_sql_exception $e) {
          echo "Error: " . $e;
        }
    } else {
      // If backups do exist, create new version & set other versions to inactive
        $set_old_inactive_sql = "UPDATE nolatin_backups SET status = 'inactive' WHERE friendly_name = '$friendly_name'";
        try {
          if (mysqli_query($conn, $set_old_inactive_sql)) {
            echo "Set old versions to inactive successfully";
          } else {
            echo "Error: " . $set_old_inactive_sql . "<br>" . mysqli_error($conn);
          }
        } catch (mysqli_sql_exception $e) {
          echo "Error: " . $e;
        }
        $version_num = count($backups);
        $post_new_sql = "INSERT INTO nolatin_backups (friendly_name, json_content, version) VALUES ('$friendly_name', '$json_content', '$version_num')";
        try {
          if (mysqli_query($conn, $post_new_sql)) {
            echo "v1 created successfully";
          } else {
            echo "Error: " . $post_new_sql . "<br>" . mysqli_error($conn);
          }
        } catch (mysqli_sql_exception $e) {
          echo "Error: " . $e;
        }
    }
  
    
  // Update original
  $update_sql = "UPDATE nolatin_exports SET json_content = '$json_content' WHERE friendly_name = '$friendly_name'";
  try {
    if (mysqli_query($conn, $update_sql)) {
      echo "Original updated successfully";
    } else {
      echo "Error: " . $update_sql . "<br>" . mysqli_error($conn);
    }
  } catch (mysqli_sql_exception $e) {
    echo "Error: " . $e;
  }

  // Close the database connection
  mysqli_close($conn);
}
?>
