<?php
session_start();

if (isset($_SESSION['username'])) {
    # database connection file
    require_once 'app/db.conn.php';
    require_once 'app/helpers/user.php';

    # Getting User data
    $user = getUser($_SESSION['username'], $conn);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Handle form submission and update user settings

        // Retrieve form data
        $newName = $_POST['name'];
        $newUsername = $_POST['username'];
        $newPassword = $_POST['password'];
        // You can retrieve other form data as needed

        // Validate the form data if needed

        // Update user settings in the database
        $updateQuery = "UPDATE users SET name = :name, username = :username, password = :password WHERE user_id = :user_id";
        $updateStmt = $conn->prepare($updateQuery);
        $updateStmt->bindValue(':name', $newName);
        $updateStmt->bindValue(':username', $newUsername);
        $updateStmt->bindValue(':password', password_hash($newPassword, PASSWORD_DEFAULT)); // Hash the new password
        $updateStmt->bindValue(':user_id', $user['user_id']);
        $updateStmt->execute();

        // Refresh the user data after updating
        $user = getUser($_SESSION['username'], $conn);
    }
} else {
    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App - Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="container w-500 p-5 shadow rounded">
        <a href="home.php" class="fs-4 link-dark">&#8592;</a>
        <h1>Settings</h1>
        <form method="POST">
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo isset($user['name']) ? htmlspecialchars($user['name']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="username" class="form-label">Email</label>
                <input type="email" class="form-control" id="username" name="username" value="<?php echo isset($user['username']) ? htmlspecialchars($user['username']) : ''; ?>">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password">
            </div>
            <!-- Add other form fields for additional settings -->

            <button type="submit" class="btn btn-primary">Save</button>
        </form>
    </div>
</body>
</html>
