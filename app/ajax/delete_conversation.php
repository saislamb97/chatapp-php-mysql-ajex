<?php
session_start();

if (isset($_SESSION['username'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Database connection file
        include '../db.conn.php';

        // Get the conversation ID from the AJAX request
        $conversationId = $_POST['conversation_id'];

        // Delete the conversation from the database
        $query = "DELETE FROM conversations WHERE conversation_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $conversationId);
        if ($stmt->execute()) {
            // Return success message
            echo "success";
        } else {
            // Return error message
            echo "error";
        }

        $stmt->close();
        $conn->close();
    }
} else {
    // Redirect if user is not logged in
    header("Location: ../index.php");
    exit;
}
?>
