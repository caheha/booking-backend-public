<?php
    include('../../headers.php');

    $json = [];
    $json["status"] = "error";

    // Import
    include('../../mySQL.php');
    include('../../JWT.php');

    // Blobals
    global $mySQL;
    global $jwt;
    global $token;

    // Validate token
    if ($jwt->validate($token)) {
        $id = isset($jwt->decode($token)->id) ? $jwt->decode($token)->id : null;

        // Get room data
        $sql = "SELECT roomId, city, zipcode, area, price, timeUnit, thumbnail FROM booking_RoomListing WHERE id = '$id'";
        $res = json_decode($mySQL->Query($sql));

        // If rooms are given, get categories and add them to the room
        if (count($res->data)) {
            foreach ($res->data as $data) {
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
            $json["rooms"] = $res->data;
        } else {
            $json["message"] = "No rooms found";
        }
    } else {
        $json["message"] = "Invalid token";
    }
    
    echo json_encode($json);
?>