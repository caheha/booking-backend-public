<?php
    header('Content-Type: application/json');

    $json = [];
    $json["status"] = "error";

    // Check if request is empty
    if (!empty($_REQUEST)) {
        // Import
        include('../../mySQL.php');

        // Globals
        global $mySQL;

        $roomId = isset($_REQUEST['roomId']) ? $_REQUEST['roomId'] : null;

        // Check if roomId is provided
        if ($roomId) {
            // Get all dates where room is reserved
            $sql = "SELECT startDate, endDate FROM booking_Reservations WHERE roomId = '$roomId'";

            $res = json_decode($mySQL->Query($sql));

            if ($res->data) {
                $json["status"] = "success";
                $json["dates"] = $res->data;
            } else {
                $json["message"] = "No dates found";
            }
        } else {
            $json["message"] = "No roomId provided";
        }
    } else {
        $json["message"] = "No request";
    }   
    
    echo json_encode($json);
?>