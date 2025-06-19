<?php session_start(); ?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <title>Warte auf BSG-Zuweisung</title>
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            background: rgba(142, 133, 118, 0.18);
            height: 100vh;
            width: 100vw;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .center-container {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            width: 70vw;
            height: 70vh;
            background: rgba(255,255,255,0.92);
            border-radius: 32px;
            box-shadow: 0 8px 48px rgba(31, 38, 135, 0.10);
            padding: 3vw 3vw 2vw 3vw;
        }
        img {
            max-width: 100%;
            max-height: 50vh;
            width: auto;
            height: auto;
            cursor: pointer;
            border-radius: 18px;
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.18);
            transition: transform 0.15s;
            display: block;
        }
        img:hover {
            transform: scale(1.03);
        }
        .delete-btn {
            display: block;
            margin: 40px auto 0 auto;
            padding: 18px 40px;
            background: linear-gradient(90deg, #e53935 0%, #b71c1c 100%);
            color: #fff;
            font-size: 1.25rem;
            font-weight: bold;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 16px rgba(229,57,53,0.12);
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: background 0.2s, transform 0.1s, box-shadow 0.2s;
        }
        .delete-btn:hover, .delete-btn:focus {
            background: linear-gradient(90deg, #b71c1c 0%, #e53935 100%);
            transform: scale(1.04);
            box-shadow: 0 6px 24px rgba(229,57,53,0.18);
            outline: none;
        }
    </style>
</head>
<body>
    <div class="center-container">
        <form method="post" action="">
            <a href="./../ypum/yfront/login.php">
                <img src="waiting.jpg" alt="Bitte warten">
            </a>
            <button type="submit" class="delete-btn" name="delete_me">Meine Registrierung löschen</button>
        </form>
    </div>
    <?php
    if (isset($_POST['delete_me'])) {
        $query = "delete from y_user where id = ?";
        $uid = $_SESSION['uid'];
        require_once(__DIR__."./../config/db_connect.php");
        $db->query($query,[$uid],0);
        header("Location: ./../ypum/yfront/login.php");
        exit;
    }
    ?>
</body>
</html>
