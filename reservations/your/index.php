<?php
    include('../../headers.php');

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

        // Get reservations combined with respective room data
        $sql = "SELECT booking_RoomListing.id AS roomId, booking_RoomListing.*, booking_Reservations.* FROM booking_Reservations INNER JOIN booking_RoomListing ON booking_Reservations.roomId = booking_RoomListing.roomId HAVING booking_Reservations.renterId = '$id' ORDER BY booking_Reservations.id DESC";
        $res = json_decode($mySQL->Query($sql));

        // Add categories to reservations based on room data
        if ($res->data) {
            foreach ($res->data as $data){
                $cat_sql = "SELECT * FROM booking_RoomCategories
                            INNER JOIN booking_Categories  
                            ON booking_RoomCategories.categoryId = booking_Categories.id
                            HAVING booking_RoomCategories.roomId = $data->roomId";

                $categories = json_decode($mySQL->Query($cat_sql));
                
                $type = array_values(array_filter($categories->data, function($obj){ 
                    return $obj->categoryType === 'type';
                }));
                
                $facilities = array_values(array_filter($categories->data, function($obj){ 
                    return $obj->categoryType === 'facilities';
                }));

                $data->categories = [
                    'type' => $type,
                    'facilities' => $facilities
                ];
            }

            $json["status"] = "success";
            $json["reservations"] = $res->data;
        } else {
            $json["message"] = "No reservations found";
        }
    } else {
        $json["message"] = "Invalid token";
    }
    
    echo json_encode($json);
?>