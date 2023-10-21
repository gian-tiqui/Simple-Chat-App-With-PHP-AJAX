<?php
session_start();

require("connection.php"); 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_SESSION['user_id'])) {
        $senderId = $_POST['sender_id'];
        $receiverId = $_POST['receiver_id'];
        $message = $_POST['message'];

        // Insert the message into the database.
        $query = "INSERT INTO private_messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $senderId, $receiverId, $message);

        if ($stmt->execute()) {
            $response = [
                'sender_id' => $senderId,
                'message' => $message,
            ];

            echo json_encode($response);
            exit();
        } else {
            $response = [
                'error' => 'Message could not be sent.',
            ];

            echo json_encode($response);
            exit();
        }
    }
}

http_response_code(400); 
echo json_encode(['error' => 'Invalid request']);
?>
