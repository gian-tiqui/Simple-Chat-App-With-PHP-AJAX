<?php
session_start();

require("connection.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['user_id'])) {
    header("Location: chat.php");
    exit();
}

$receiverUserId = $_GET['user_id'];
$loggedInUserId = $_SESSION['user_id'];

$query = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();
$loggedInUser = $result->fetch_assoc();

$query = "SELECT username FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $receiverUserId);
$stmt->execute();
$result = $stmt->get_result();
$receiverUser = $result->fetch_assoc();

$query = "SELECT sender_id, message FROM private_messages WHERE
          (sender_id = ? AND receiver_id = ?) OR (sender_id = ? AND receiver_id = ?) ORDER BY created_at";
$stmt = $conn->prepare($query);
$stmt->bind_param("iiii", $loggedInUserId, $receiverUserId, $receiverUserId, $loggedInUserId);
$stmt->execute();
$result = $stmt->get_result();
$messages = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Private Chat Room</title>
    <style>
    .message {
        padding: 5px;
        margin: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
        max-width: 80%;
    }

    .sender-message {
        background-color: #cce6ff;
        float: right;
        clear: both; 
    }

    .receiver-message {
        background-color: #e6ffe6;
        float: left;
        clear: both; 
    }
</style>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</head>
<body>
    <div class="container">
    <h1> hello <?php echo $loggedInUser['username']; ?> </h1>
    <a href="chat.php" class="btn btn-danger">Go back</a>
        <div class="row mt-5">
            <div class="container col bg-dark card p-2">
                <h1 class="text-white"><?php echo $receiverUser['username']; ?></h1>

                <div id="chat-container" class="p-1 bg-secondary" style="height: 300px; overflow-y: scroll;">
                    <?php
                    foreach ($messages as $message) {
                        $messageClass = $message['sender_id'] == $loggedInUserId ? 'sender-message' : 'receiver-message';
                        echo "<div class='message $messageClass'>" . $message['message'] . "</div>";
                    }
                    ?>
                </div>

                <form method="post" id="message-form" class="form-control mt-2">
                    <div class="input-group">
                        <input type="text" id="message" name="message" class="form-control" placeholder="Type your message..." required />
                        <input type="hidden" name="receiver_id" value="<?php echo $receiverUserId; ?>" />
                        <input type="hidden" name="sender_id" value="<?php echo $loggedInUserId; ?>" />
                        <div class="input-group-append">
                            <button type="submit" id="send-button" class="btn btn-primary">Send</button>
                        </div>
                    </div>
                </form>
            </div>
            
        </div>
        
    </div>
</body>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

function sendMessage() {
    var message = $("#message").val();

    if (message.trim() === "") {
        alert("Please enter a message.");
        return;
    }

    $.ajax({
        type: "POST",
        url: "send_message.php",
        data: {
            sender_id: <?php echo $loggedInUserId; ?>,
            receiver_id: <?php echo $receiverUserId; ?>,
            message: message
        },
        dataType: "json",
        success: function(response) {
            if (response.error) {
                alert("Error: " + response.error);
            } else {
                var messageClass = response.sender_id == <?php echo $loggedInUserId; ?> ? 'sender-message' : 'receiver-message';
                var newMessage = "<div class='message " + messageClass + "'>" + response.message + "</div>";
                $("#chat-container").append(newMessage);

                // Scroll to the bottom of the chat container.
                $("#chat-container").scrollTop($("#chat-container")[0].scrollHeight);

                $("#message").val("");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log("AJAX Error: " + errorThrown);
            alert("An error occurred while sending the message.");
        }
    });
}

function fetchNewMessages() {
    $.ajax({
        type: "POST",
        url: "fetch_new_messages.php", 
        data: {
            receiver_id: <?php echo $loggedInUserId; ?>,
            sender_id: <?php echo $receiverUserId; ?>
        },
        dataType: "json",
        success: function(response) {
            if (!response.error && response.messages.length > 0) {
                response.messages.forEach(function (message) {
                    var messageClass = message.sender_id == <?php echo $receiverUserId; ?> ? 'sender-message' : 'receiver-message';
                    var newMessage = "<div class='message " + messageClass + "'>" + message.message + "</div>";
                    $("#chat-container").append(newMessage);
                });

                // Scroll to the bottom of the chat container.
                $("#chat-container").scrollTop($("#chat-container")[0].scrollHeight);
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.log("AJAX Error: " + errorThrown);
        }
    });
}

$("#message-form").submit(function(e) {
    e.preventDefault();
    sendMessage();
});

$("#chat-container").scrollTop($("#chat-container")[0].scrollHeight);

setInterval(fetchNewMessages, 3000); 
</script>

</html>
