<?php
    include('../headers.php');

    $json = [];
    $json["status"] = "error";

    // Import
    include('../mySQL.php');

    // Globals
    global $mySQL;

    // Get all categoires
    $sql = "SELECT * FROM booking_Categories";
    $res = json_decode($mySQL->Query($sql));

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
    
    echo json_encode($json);
?>