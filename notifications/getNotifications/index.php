<?php
    header('Content-Type: application/json');

    $json = [];
    $json["status"] = "error";

    // Import
    include('../../mySQL.php');
    include('../../JWT.php');

    // Globals
    global $mySQL;
    global $jwt;
    global $token;

    // Validate token
    if ($jwt->validate($token)) {
        $id = isset($jwt->decode($token)->id) ? $jwt->decode($token)->id : null;

        // Get notifications based on user id
        $sql = "SELECT id, title, note, destination, noticeType, seen, createdAt FROM booking_notifications WHERE userId = '$id' ORDER BY createdAt DESC";
        $res = json_decode($mySQL->Query($sql));

        if ($res->data) {
            $json["status"] = "success";
            $json["notifications"] = $res->data;
        } else {
            $json["status"] = "error";
            $json["message"] = "No notifications found";
        }
    } else {
        $json["message"] = "Invalid token";
    }
    
    echo json_encode($json);
?>