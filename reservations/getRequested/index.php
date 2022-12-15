<?php
    header('Content-Type: application/json');

    $json = [];
    $json['status'] = 'error';

    // Import
    include('../../mySQL.php');
    include('../../JWT.php');

    // Global
    global $mySQL;
    global $jwt;
    global $token;

    // Validate token
    if ($jwt->validate($token)) {
        $id = isset($jwt->decode($token)->id) ? $jwt->decode($token)->id : null;

        // Check if id is provided
        if ($id) {
            // Get requested reservations from rooms that you own
            $sql = "SELECT booking_Reservations.*, booking_userProfile.avatar, booking_userProfile.firstname, booking_userProfile.lastname, booking_RoomListing.id AS ownerId, booking_RoomListing.thumbnail, booking_RoomListing.city, booking_RoomListing.roomAddress, booking_RoomListing.zipcode, booking_RoomListing.roomId, booking_RoomListing.price, booking_RoomListing.timeUnit, booking_RoomListing.startTime, booking_RoomListing.endTime FROM ((booking_Reservations INNER JOIN booking_RoomListing ON booking_Reservations.roomId = booking_RoomListing.roomId) INNER JOIN booking_userProfile ON booking_Reservations.renterId = booking_userProfile.id) HAVING booking_RoomListing.id = '$id'";
            $response = json_decode($mySQL->Query($sql));

            if (count($response->data)) {
                $json["status"] = "success";
                $json["reservations"] = $response->data;
            } else {
                $json["message"] = "No reservations found";
            }
        } else {
            $json['message'] = 'No token';
        }
    } else {
        $json['message'] = 'invalid token';
    }

    echo json_encode($json);
?>