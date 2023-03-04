<?php
include ('../../wo-config.php');
$conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

// Check if the connection was successful
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Query the database to get the table data
$sql = "SELECT * FROM nolatin_exports order by id asc";

$result = mysqli_query($conn, $sql);

// Output the table with the field name "friendly_url"
echo "<table>";
echo "<tr><th>friendly_url</th><th>id</th></tr>";

while ($row = mysqli_fetch_assoc($result)) {
    echo "<tr><td><a href='/json/?friendly_url=" . $row["friendly_url"] . ">". $row["friendly_url"] ."</a></td><td>" . $row["id"] . "</td></tr>";
}

echo "</table>";

// Close the database connection
mysqli_close($db);
?>
