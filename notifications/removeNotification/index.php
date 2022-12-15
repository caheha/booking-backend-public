<?php
    include('../../headers.php');

    $json = [];
    $json["status"] = "error";

    // Check if request is empty
    if (!empty($_REQUEST)) {
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
            $notificationId = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
    
            // Check if all values are provided
            if ($notificationId && $id) {
                $sql = "DELETE FROM booking_notifications WHERE id = '$notificationId' AND userId = '$id'";
                $res = $mySQL->Query($sql, false);
        
                if ($res) {
                    $json["status"] = "success";
                    $json["message"] = "Notification removed";
                } else {
                    $json["message"] = "No notification found";
                }
            } else {
                $json["message"] = "Missing arguments";
            }
        } else {
            $json["message"] = "Invalid token";
        }
    }

    
    
    echo json_encode($json);
?>