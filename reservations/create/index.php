<?php
    header('Content-Type: application/json');

    $json = [];
    $json['status'] = 'error';

    // Check if request is empty
    if(!empty($_REQUEST)) {
        // import
        include('../../mySQL.php');
        include('../../JWT.php');
    
        // Globals
        global $mySQL;
        global $jwt;
        global $token;
    
        // Validate token
        if ($jwt->validate($token)) {
            $id = isset($jwt->decode($token)->id) ? $jwt->decode($token)->id : null;
            $roomId = isset($_REQUEST['roomId']) ? $_REQUEST['roomId'] : null;
            $startDate = isset($_REQUEST['startDate']) ? $_REQUEST['startDate'] : null;
            $endDate = isset($_REQUEST['endDate']) ? $_REQUEST['endDate'] : null;

            // Check if all values are provided
            if ($id && $roomId && $startDate && $endDate) {
                
                // Check if user exists
                $response = json_decode($mySQL->Query("SELECT id FROM booking_userLogin WHERE id = '$id' LIMIT 1"));
    
                if (count($response->data)) {
                    $renterId = $id;
                    
                    // Check if room is occupied in requested timespan
                    $occupied = json_decode($mySQL->Query("SELECT * FROM booking_Reservations WHERE roomId = $roomId AND (('$startDate' BETWEEN startDate AND endDate) OR ('$endDate' BETWEEN startDate AND endDate) OR ('$startDate' < startDate AND '$endDate' > endDate))"));

                    if(!count($occupied->data)){
                        // Create reservation
                        $res = $mySQL->Query("CALL booking_CreateReservation('$roomId', '$startDate', '$endDate', '$renterId')", false);
                    
                        if ($res) {
                            $json['status'] = 'success';
                            $json['message'] = 'Reservation oprettet';
                        } else {
                            $json['message'] = 'Server error';
                        }
                    }else{
                        $json['message'] = 'Unavailable in chosen timeframe';
                    }
                } else {
                    $json['message'] = 'User not valid';
                }
            } else {
                $json['message'] = 'Inputs not filled in';
            }
        } else {
            $json['message'] = 'invalid token';
        }
    } else {
        $json['message'] = 'No request';
    }
    
    echo json_encode($json);
?>