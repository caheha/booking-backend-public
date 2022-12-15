<?php
    include('../../headers.php');

    $json = [];
    $json["status"] = "error";

    // Import
    include('../../mySQL.php');

    // Globals
    global $mySQL;

    // Check if request is empty
    if (!empty($_REQUEST)) {
        $roomId = isset($_REQUEST['roomId']) ? $_REQUEST['roomId'] : null;

        // Check if room id is provided
        if ($roomId) {
            $sql = "SELECT booking_RoomListing.*, booking_userProfile.firstname, booking_userProfile.lastname, booking_userProfile.avatar, booking_userProfile.phone FROM booking_RoomListing INNER JOIN booking_userProfile ON booking_RoomListing.id = booking_userProfile.id HAVING roomId = '$roomId' LIMIT 1;";
            $res = json_decode($mySQL->Query($sql));

            // Check if room data exists
            if (count($res->data)) {
                // Add categories to room data
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
                $json["room"] = $res->data[0];
            } else {
                $json["message"] = "No room found";
            }
        } else {
            $json["message"] = "No roomId provided";
        }
    } else {
        $json["message"] = "No request";
    }   
    
    echo json_encode($json);
?>