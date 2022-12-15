<?php
    include('../../headers.php');

    $json = [];
    $json["status"] = "error";

    // Check if request is empty
    if (!empty($_REQUEST)) {
        // Import
        include('../../mySQL.php');

        // Globals
        global $mySQL;

        $roomId = isset($_REQUEST['roomId']) ? $_REQUEST['roomId'] : null;

        // Check room id is provided
        if ($roomId) {
            // Get room data and categories for room
            $sql = "SELECT booking_Categories.* FROM `booking_RoomCategories` INNER JOIN booking_Categories ON booking_RoomCategories.categoryId = booking_Categories.id AND booking_RoomCategories.roomId = '$roomId';";
            $res = json_decode($mySQL->Query($sql));

            // If room exists
            if ($res) {
                // Split categories into type and facilities
                $type = array_values(array_filter($res->data, function($obj){ 
                    return $obj->categoryType === 'type';
                }));

                $facilities = array_values(array_filter($res->data, function($obj){ 
                    return $obj->categoryType === 'facilities';
                }));
                
                $json["status"] = "success";
                $json["categories"] = [
                    'type' => $type,
                    'facilities' => $facilities,
                ];
                
            } else {
                $json["message"] = "No categories found";
            }
        }else {
            $json["message"] = "No roomId provided";
        }
    } else {
        $json["message"] = "No request";
    }   
    echo json_encode($json);
?>