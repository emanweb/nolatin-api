// list of allowed IP addresses
$allowed_ips = array('184.171.244.81', '68.108.129.180');

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

  $v0_friendly_name = $friendly_name + "/v0"
  
  // Construct the v0 SQL query
  $v0_sql = "INSERT INTO nolatin_exports (friendly_name, json_content, emailaddress) VALUES ('$v0_friendly_name', '$json_content', '$emailaddress')";
  
  // Create v0
  try {
    if (mysqli_query($conn, $v0_sql)) {
      echo "Version 0 created successfully";
    } else {
      echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
  } catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1062) { // 1062 is the MySQL error code for duplicate entry
      // Handle the duplicate entry error here
      echo "Error: Duplicate key. Version 0 already exists";
      // You may want to display an error message to the user or log the error
    } else {
      // Handle other MySQL errors here
    }
  }
  
  // Construct the update SQL queries
  $update_sql = "UPDATE nolatin_exports SET json_content = '$json_content' WHERE friendly_name = '$friendly_name'";

  // Update original
  try {
    if (mysqli_query($conn, $update_sql)) {
      echo "Original updated successfully";
    } else {
      echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
  } catch (mysqli_sql_exception $e) {
    echo "Error: " . $e;
  }

  
  // TO_DO: FINISH THIS PORTION
  // Construct the new version SQL queries 
  $new_version_friendly_name = $friendly_name + "/v1"
  $new_version_sql = "INSERT INTO nolatin_exports (friendly_name, json_content, emailaddress) VALUES ('$new_version_friendly_name', '$json_content', '$emailaddress')";

  // Create new version backup
  try {
    if (mysqli_query($conn, $update_sql)) {
      echo "Original updated successfully";
    } else {
      echo "Error: " . $sql . "<br>" . mysqli_error($conn);
    }
  } catch (mysqli_sql_exception $e) {
    echo "Error: " . $e;
  }

  // Close the database connection
  mysqli_close($conn);
}
?>
