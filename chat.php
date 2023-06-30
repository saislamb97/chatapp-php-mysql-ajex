<?php
session_start();

if (isset($_SESSION['username'])) {
    include 'app/db.conn.php';

    include 'app/helpers/user.php';
    include 'app/helpers/chat.php';
    include 'app/helpers/opened.php';

    include 'app/helpers/timeAgo.php';

    if (!isset($_GET['user'])) {
        header("Location: home.php");
        exit;
    }

    $chatWith = getUser($_GET['user'], $conn);

    if (empty($chatWith)) {
        header("Location: home.php");
        exit;
    }

    $chats = getChats($_SESSION['user_id'], $chatWith['user_id'], $conn);

    opened($chatWith['user_id'], $conn, $chats);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat App</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" href="css/style.css">
    <link rel="icon" href="img/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .chat-box {
            overflow-y: auto;
            max-height: 300px;
        }

        .message {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 8px;
            margin-bottom: 10px;
        }

        .message.sent {
            align-self: flex-end;
            background-color: #dcf8c6;
        }

        .message.received {
            align-self: flex-start;
            background-color: #fff;
        }
        .fa-paper-plane {
            width: 100px;
        }
    </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100">
    <div class="p-2 w-500 rounded shadow d-flex">
        <div class="w-100">
                <a href="home.php" class="fs-4 link-dark">&#8592;</a>

                <div class="d-flex align-items-center">
                    <img src="uploads/<?= $chatWith['p_p'] ?>" class="w-15 rounded-circle">

                    <h3 class="display-4 fs-sm m-2">
                        <b><?= $chatWith['name'] ?></b><br>
                        <div class="d-flex align-items-center" title="online">
                            <?php
                                if (last_seen($chatWith['last_seen']) == "Active") {
                            ?>
                                <div class="online"></div>
                                <small class="d-block p-1">Online</small>
                            <?php } else { ?>
                                <small class="d-block p-2 text-secondary">
                                    Last seen:
                                    <?= last_seen($chatWith['last_seen']) ?>
                                </small>
                            <?php } ?>
                        </div>
                    </h3>
                </div>

                <div class="shadow p-4 rounded d-flex flex-column mt-2 chat-box bg-light" id="chatBox">
                    <?php 
                        if (!empty($chats)) {
                        foreach($chats as $chat){
                            if($chat['from_id'] == $_SESSION['user_id']) { ?>
                                <div class="message sent">
                                    <?= $chat['message'] ?> 
                                    <small class="d-block">
                                        <?= $chat['created_at'] ?>
                                    </small>
                                </div>
                            <?php } else { ?>
                                <div class="message received">
                                    <?= $chat['message'] ?> 
                                    <small class="d-block">
                                        <?= $chat['created_at'] ?>
                                    </small>
                                </div>
                            <?php } 
                        }   
                    } else { ?>
                        <div class="alert alert-info text-center">
                            <i class="fa-sharp fa-solid fa-comments"></i>
                            No messages yet, start the conversation
                        </div>
                    <?php } ?>
                </div>

                <div class="input-group mb-3">
                    <textarea cols="3" id="message" class="form-control"></textarea>
                    <button class="btn btn-primary" id="sendBtn">
                        <i class="fa fa-paper-plane"></i>
                    </button>
                </div>
        </div>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>

    <script>
        var scrollDown = function(){
            let chatBox = document.getElementById('chatBox');
            chatBox.scrollTop = chatBox.scrollHeight;
        }

        scrollDown();

        $(document).ready(function(){
            $("#sendBtn").on('click', function(){
                message = $("#message").val();
                if (message == "") return;

                $.post("app/ajax/insert.php", {
                        message: message,
                        to_id: <?=$chatWith['user_id']?>
                    },
                    function(data, status){
                        $("#message").val("");
                        $("#chatBox").append(data);
                        scrollDown();
                    });
            });

            let lastSeenUpdate = function(){
                $.get("app/ajax/update_last_seen.php");
            }
            lastSeenUpdate();

            setInterval(lastSeenUpdate, 10000);

            let fetchData = function(){
                $.post("app/ajax/getMessage.php", {
                        id_2: <?=$chatWith['user_id']?>
                    },
                    function(data, status){
                        $("#chatBox").append(data);
                        if (data != "") scrollDown();
                    });
            }

            fetchData();

            setInterval(fetchData, 500);
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
