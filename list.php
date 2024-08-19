<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>No Latin API - List of Prototypes.</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@3.3.7/dist/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>
<body>
	<div class="container">
		<h1>List of Prototypes Saved</h1>
        <p><a href="form.php">Add new data manually</a></p>

<?php include ('../../wo-config.php'); $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
// Check if the connection was successful
if (!$conn) { die("Connection failed: " . mysqli_connect_error());
}
// Query the database to get the table data
$sql = "SELECT * FROM nolatin_exports order by id asc"; $result = mysqli_query($conn, $sql);
// Output the table with the field name "friendly_url"
echo "<table class='table table-striped'>"; echo "<thead><tr><th scope='col'>row number</th><th scope='col'>friendly_name</th><th scope='col'>id</th><th scope='col'>date</th></tr></thead><tbody>"; 
$row_number = 1; 
while ($row = mysqli_fetch_assoc($result)) 
{ echo "<tr><td>".$row_number."</td><td><a href='/json/?friendly_name=" . 
$row["friendly_name"] . "'>". 
$row["friendly_name"] ."</a></td><td>" . $row["id"] . "</td><td>".$row["datetime"]."</td></tr>";
$row_number ++;
}
echo "</tbody></table>";
// Close the database connection
mysqli_close($conn);
?>
	</div>
</body>
</html>
