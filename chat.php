<?php
session_start();

require("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['logout'])) {
    // Clear the session and redirect to the login page when the logout button is clicked.
    session_destroy();
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $selectedUserId = $_POST['selectedUser'];

    header("Location: chat_room.php?user_id=" . $selectedUserId);
    exit();
}

$query = "SELECT id, username FROM users WHERE id != ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<h1>Chat Page</h1>

<!-- Logout button -->
<form method="post" action="chat.php">
    <button type="submit" name="logout">Logout</button>
</form>

<form method="post" action="chat.php">
    <label for="selectedUser">Select a user to chat with:</label>
    <select name="selectedUser" id="selectedUser">
        <?php foreach ($users as $user) { ?>
            <option value="<?php echo $user['id']; ?>"><?php echo $user['username']; ?></option>
        <?php } ?>
    </select>
    <button type="submit">Chat</button>
</form>
