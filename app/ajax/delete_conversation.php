<?php
session_start();

if (isset($_SESSION['username'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Database connection file
        include '../db.conn.php';

        // Get the conversation ID from the AJAX request
        $conversationId = $_POST['conversation_id'];

        // Delete the conversation from the database using PDO
        $pdoQuery = "DELETE FROM conversations WHERE conversation_id = ?";
        $pdoStmt = $pdoConn->prepare($pdoQuery);

        if (!$pdoStmt) {
            // Return error message for query preparation failure
            echo "Error preparing PDO query: " . $pdoConn->errorInfo()[2];
            exit;
        }

        $pdoStmt->bindParam(1, $conversationId, PDO::PARAM_INT);
        if ($pdoStmt->execute()) {
            // Return success message
            echo "success";
        } else {
            // Return error message
            echo "error";
        }

        $pdoStmt->closeCursor();

        // Close the PDO connection
        $pdoConn = null;
    }
} else {
    // Redirect if user is not logged in
    header("Location: ../index.php");
    exit;
}
?>
