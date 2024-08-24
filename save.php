<?php
header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json');

// Function to log messages
function logMessage($message, $type = 'INFO') {
    $logFile = 'app.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] [$type] $message\n", FILE_APPEND);
}

if (!isset($_POST['localform'])) {
    $rest_json = file_get_contents("php://input");
    $_POST = json_decode($rest_json, true);
}

if ($_POST) {
    // Input validation
    $friendly_name = filter_var($_POST["friendly_name"] ?? '', FILTER_SANITIZE_STRING);
    $json_content = $_POST["json_content"] ?? '';
    $emailaddress = filter_var($_POST["emailaddress"] ?? '', FILTER_VALIDATE_EMAIL);

    if (empty($friendly_name) || empty($json_content) || !$emailaddress) {
        $response = ["error" => "Invalid or missing input data"];
        echo json_encode($response);
        logMessage("Invalid input data received", "ERROR");
        exit;
    }

    // Connect to MySQL database
    include('../../wo-config.php');
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);

    if (!$conn) {
        $response = ["error" => "Database connection failed"];
        echo json_encode($response);
        logMessage("Database connection failed: " . mysqli_connect_error(), "ERROR");
        exit;
    }

    // Prepare statement for insertion
    $insert_stmt = $conn->prepare("INSERT INTO nolatin_exports (friendly_name, json_content, emailaddress) VALUES (?, ?, ?)");
    $insert_stmt->bind_param("sss", $friendly_name, $json_content, $emailaddress);

    // Prepare statement for update
    $update_stmt = $conn->prepare("UPDATE nolatin_exports SET json_content = ?, emailaddress = ? WHERE friendly_name = ?");
    $update_stmt->bind_param("sss", $json_content, $emailaddress, $friendly_name);

    // Prepare statement for email check
    $email_check_stmt = $conn->prepare("SELECT emailaddress FROM nolatin_exports WHERE friendly_name = ?");
    $email_check_stmt->bind_param("s", $friendly_name);

    try {
        if ($insert_stmt->execute()) {
            $response = ["success" => "Your link was created successfully"];
            logMessage("New entry created: $friendly_name");
        }
    } catch (mysqli_sql_exception $e) {
        if ($e->getCode() == 1062) { // Duplicate entry
            $email_check_stmt->execute();
            $result = $email_check_stmt->get_result();
            $existing_email = $result->fetch_assoc()['emailaddress'];

            if ($existing_email == $emailaddress) {
                if ($update_stmt->execute()) {
                    $response = ["success" => "Your link was updated successfully"];
                    logMessage("Entry updated: $friendly_name");
                } else {
                    $response = ["error" => "Failed to update existing entry"];
                    logMessage("Failed to update entry: $friendly_name", "ERROR");
                }
            } else {
                $response = ["error" => "Friendly name already exists with a different email"];
                logMessage("Duplicate friendly name attempt: $friendly_name", "WARNING");
            }
        } else {
            $response = ["error" => "An error occurred while processing your request"];
            logMessage("MySQL Error: " . $e->getMessage(), "ERROR");
        }
    }

    echo json_encode($response);

    $insert_stmt->close();
    $update_stmt->close();
    $email_check_stmt->close();
    mysqli_close($conn);
} else {
    $response = ["error" => "No POST data received"];
    echo json_encode($response);
    logMessage("No POST data received", "WARNING");
}
?>