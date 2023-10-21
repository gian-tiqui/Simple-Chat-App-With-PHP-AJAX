<?php
session_start();

require("connection.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $query = "SELECT id FROM users WHERE username = ? AND password = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();

    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
  
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        header("Location: chat.php"); 
        exit();
    } else {
        echo "Invalid username or password"; 
    }
}
?>

<h1>Login</h1>

<form method="post" action="login.php">
    <input type="text" name="username" placeholder="Username" required>
    <input type="password" name="password" placeholder="Password" required>
    <button type="submit">Login</button>
</form>
