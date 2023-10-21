<?php
session_start();

require("connection.php"); // Include your database connection code.

if (!isset($_SESSION['user_id'])) {
    // User is not authenticated. You can handle this as needed.
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $receiver_id = $_POST['receiver_id'];
    $sender_id = $_POST['sender_id'];

    $query = "SELECT sender_id, message FROM private_messages WHERE receiver_id = ? AND sender_id = ? AND created_at > ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iis", $receiver_id, $sender_id, $_POST['last_checked_time']);

    $stmt->execute();
    $result = $stmt->get_result();

    $newMessages = [];
    while ($row = $result->fetch_assoc()) {
        $newMessages[] = $row;
    }

    if (count($newMessages) > 0) {
        $currentTime = date("Y-m-d H:i:s");
        $response = [
            'messages' => $newMessages,
            'last_checked_time' => $currentTime
        ];
    } else {
        $response = [
            'messages' => [],
            'last_checked_time' => $_POST['last_checked_time'] 
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($response);
} else {
   
}
?>
