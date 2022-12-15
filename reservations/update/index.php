<?php
    include('../../headers.php');

    $json = [];
    $json['status'] = 'error';

    // Check if request is empty
    if(!empty($_REQUEST)) {
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
            $reservationId = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
            $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : null;

            // Check if all values are provided
            if ($id && $reservationId && $status) {
                //Verify user owns room or reservation
                $reservationResponse = json_decode($mySQL->Query("SELECT roomId, renterId FROM booking_Reservations WHERE id = '$reservationId' LIMIT 1"));

                if (count($reservationResponse->data)) {
                    $roomId = $reservationResponse->data[0]->roomId;

                    $userIdresponse = json_decode($mySQL->Query("SELECT id FROM booking_RoomListing WHERE roomId = '$roomId' LIMIT 1"));

                    if (count($userIdresponse->data)) {
                        $userId = $userIdresponse->data[0]->id;
                        $renterId = $reservationResponse->data[0]->renterId;

                        if ($id === $userId || ($id === $reservationResponse->data[0]->renterId && $status === 'canceled')) {
                            // Update status of reservation
                            $res = $mySQL->Query("UPDATE booking_Reservations
                                                  SET reservationStatus = '$status'
                                                  WHERE id = '$reservationId'", false);

                            if ($res) {
                                $json['status'] = 'success';
                                $json['message'] = 'Status opdateret';

                                // Add notifcation
                                if($status === 'accepted'){
                                    $reservationNote = $mySQL->Query("INSERT INTO booking_notifications (userId, title, note, destination, noticeType) VALUES('$renterId','Accepteret', 'En af dine reserveringer er godkendt', '/reservationer', 'success')", false);
                                    $json['test'] = $reservationNote;
                                }else{
                                    $reservationNote = $mySQL->Query("INSERT INTO booking_notifications (userId, title, note noticeType) VALUES('$renterId','Afmeldt', 'En af dine reserveringer er afmeldt', 'warning')", false);
                                    $json['test'] = $reservationNote;                                    
                                }

                            } else {
                                $json['message'] = 'Server error';
                            }
                        } else {
                            $json['message'] = 'User not owner of the room';
                        }
                    } else {
                        $json['message'] = "No user found";
                    }
                } else {
                    $json['message'] = 'No room found';
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