<?php
// Step 1: Connect to the MySQL database
include ('../../wo-config.php');
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
// Step 2: Write a SQL query to fetch the data from the MySQL table
$friendly_name = mysqli_real_escape_string($conn, $_GET['friendly_name']);
$sql = "SELECT json_content FROM nolatin_exports where friendly_name  = '".$friendly_name."'";
// Step 3: Execute the SQL query using PHP
$result = mysqli_query($conn, $sql);
while($row = mysqli_fetch_assoc($result)) {
    $json_data = stripslashes($row['json_content']);
}

// Step 6: Set the Content-Type header to application/json
header('Content-Type: application/json');
// Step 7: Output the JSON string
echo $json_data;

// Close the database connection
mysqli_close($conn);
?>

