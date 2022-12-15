<?php 
    header('Content-Type: application/json');

    
    $json = [];
    $json["status"] = "error";
    
    // Import
    include('../../mySQL.php');
    include('../../JWT.php');

    // Include
    global $mySQL;
    global $jwt;
    global $token;

    // Validate token
    if ($jwt->validate($token)) {
        $id = isset($jwt->decode($token)->id) ? $jwt->decode($token)->id : null;
        $roomId = isset($_REQUEST['roomId']) ? $_REQUEST['roomId'] : null;

        // Check if all values are provided
        if ($id && $roomId) {
            // Get thumbnail path
            $response = json_decode($mySQL->Query("SELECT thumbnail FROM booking_RoomListing WHERE roomId = '$roomId' LIMIT 1"));
            
            // Remove image if it exists
            if (count($response->data)) {
                if ($response->data[0]->thumbnail) {
                    $file_path = '../../' . $response->data[0]->thumbnail;

                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }

                // Delete Room Categories
                $sql = "DELETE FROM booking_RoomCategories WHERE roomId = '$roomId'"; 
                $mySQL->Query($sql, false);
                
                // Delete Room Listing
                $sql = "DELETE FROM booking_RoomListing WHERE roomId = '$roomId'"; 
                $result = $mySQL->Query($sql, false);
    
                if ($result) {
                    $json['status'] = 'success';
                    $json['message'] = "Room successfully deleted";
                } else {
                    $json['message'] = "Server error";
                }
            } else {
                $json['message'] = "No user exist";
            }
        } else {
            $json['message'] = "Manglende argumenter";
        }  
    } else {
        $json['message'] = "Invalid token";
    } 

    echo json_encode($json);
?>