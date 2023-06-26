<?php
session_start();

if (isset($_SESSION['username'])) {
    // Database connection file
    include 'app/db.conn.php';

    include 'app/helpers/user.php';
    include 'app/helpers/conversations.php';
    include 'app/helpers/timeAgo.php';
    include 'app/helpers/last_chat.php';

    // Getting User data
    $user = getUser($_SESSION['username'], $conn);
    if (!$user || !isset($user['user_id'])) {
        // Redirect to login page if user data is not found
        session_destroy();
        header("Location: index.php");
        exit;
    }

    // Getting User conversations
    $conversations = getConversation($user['user_id'], $conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App - Home</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .logout-btn {
            background-color: #212529;
            color: #fff;
            margin-right: 10px;
        }

        .settings-btn {
            background-color: #007bff;
            color: #fff;
        }

        .delete-btn {
            padding: 5px;
            font-size: 15px;
            line-height: 1;
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="p-2 w-500 rounded shadow">
        <div>
            <div class="d-flex mb-3 p-3 bg-light justify-content-between align-items-center">
                <div class="d-flex align-items-center">
                    <img src="uploads/<?= isset($user['p_p']) ? $user['p_p'] : '' ?>" class="w-25 rounded-circle">
                    <h3 class="fs-xs m-2"><?= isset($user['name']) ? $user['name'] : '' ?></h3>
                </div>
                <a href="logout.php" class="btn btn-dark logout-btn">Logout</a>
                <a href="settings.php" class="btn btn-primary settings-btn">Settings</a>
            </div>

            <div class="input-group mb-3">
                <input type="text" placeholder="Search..." id="searchText" class="form-control">
                <button class="btn btn-primary" id="searchBtn">
                    <i class="fa fa-search"></i>    
                </button>
            </div>
            <ul id="chatList" class="list-group mvh-50 overflow-auto">
                <?php if (!empty($conversations)) { ?>
                    <?php foreach ($conversations as $conversation) { ?>
                        <li class="list-group-item">
                            <div class="d-flex align-items-center">
                                <div>
                                    <button class='btn btn-danger delete-btn' data-conversation-id='<?= $conversation['conversation_id'] ?>'>
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                                <a href="chat.php?user=<?= $conversation['username'] ?>" class="d-flex justify-content-between align-items-center p-2">
                                    <div class="d-flex align-items-center">
                                        <img src="uploads/<?= isset($conversation['p_p']) ? $conversation['p_p'] : '' ?>" class="w-10 rounded-circle">
                                        <h3 class="fs-xs m-2">
                                            <?= isset($conversation['name']) ? $conversation['name'] : '' ?><br>
                                            <small>
                                                <?php 
                                                    echo lastChat($user['user_id'], $conversation['user_id'], $conn);
                                                ?>
                                            </small>
                                        </h3>
                                    </div>
                                    <?php if (last_seen($conversation['last_seen']) == "Active") { ?>
                                        <div title="online">
                                            <div class="online"></div>
                                        </div>
                                    <?php } ?>
                                </a>
                            </div>
                        </li>
                    <?php } ?>
                <?php } else { ?>
                    <div class="alert alert-info text-center">
                        <i class="fa fa-comments d-block fs-big"></i>
                        No messages yet, start the conversation
                    </div>
                <?php } ?>
            </ul>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
        $(document).ready(function() {
            // Search
            $("#searchText").on("input", function() {
                var searchText = $(this).val();
                if (searchText == "") return;
                $.post('app/ajax/search.php', {
                        key: searchText
                    },
                    function(data, status) {
                        $("#chatList").html(data);
                    });
            });

            // Search using the button
            $("#searchBtn").on("click", function() {
                var searchText = $("#searchText").val();
                if (searchText == "") return;
                $.post('app/ajax/search.php', {
                        key: searchText
                    },
                    function(data, status) {
                        $("#chatList").html(data);
                    });
            });

            // Delete conversation
            $(document).on("click", ".delete-btn", function() {
                var conversationId = $(this).data("conversation-id");
                if (!confirm("Are you sure you want to delete this conversation?")) {
                    return;
                }
                $.post("app/ajax/delete_conversation.php", { conversation_id: conversationId }, function(data, status) {
                    if (status === "success" && data === "success") {
                        // Remove the conversation from the UI
                        $(this).closest("li").remove();
                    } else {
                        alert("Failed to delete the conversation. Please try again.");
                    }
                });
            });

            /**
            auto update last seen
            for logged in user
            **/
            let lastSeenUpdate = function() {
                $.get("app/ajax/update_last_seen.php");
            }
            lastSeenUpdate();
            /**
            auto update last seen
            every 10 sec
            **/
            setInterval(lastSeenUpdate, 10000);
        });
    </script>
</body>
</html>

<?php
} else {
    header("Location: index.php");
    exit;
}
?>
