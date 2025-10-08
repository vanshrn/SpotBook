<?php
require_once 'db_config.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $clientRole = $_POST['role'] ?? 'customer'; // The role the client attempted to log in as

    try {
        // Fetch user data, including the role
        $stmt = $pdo->prepare("SELECT id, password, role FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Check if the user's actual role matches the role they tried to log in as
            if ($user['role'] !== $clientRole) {
                 echo json_encode(['status' => 'error', 'message' => "You are registered as a " . ucfirst($user['role']) . ", not a " . ucfirst($clientRole) . "."]);
                 exit;
            }
            
            // Password is correct and role matches, start a session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role']; // Store the role in the session

            // Determine redirect path based on role
            $redirectPath = ($user['role'] === 'customer') ? 'categories.html' : 'shop_dashboard.html';

            echo json_encode(['status' => 'success', 'message' => 'Login successful!', 'redirect' => $redirectPath]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Invalid username or password.']);
        }
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => 'An error occurred during login.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>