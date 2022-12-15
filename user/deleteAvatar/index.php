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

    // VAlidate token
    if ($jwt->validate($token)) {
        $id = isset($jwt->decode($token)->id) ? $jwt->decode($token)->id : null;

        // Check if id exists
        if ($id) {
            $response = json_decode($mySQL->Query("SELECT avatar FROM booking_userProfile WHERE id = '$id' LIMIT 1"));

            // Check if user exists
            if (count($response->data)) {
                $file_path = '../../' . $response->data[0]->avatar;
                
                // if File exists, remove it
                if (file_exists($file_path)) {
                    unlink($file_path);
                }

                // Update table
                if ($file_path) {
                    $sql = "UPDATE booking_userProfile SET avatar = '' WHERE id = '$id'"; 
                    $result = $mySQL->Query($sql, false);
        
                    if ($result) {
                        $json['status'] = 'success';
                        $json['message'] = "Avatar successfully deleted";
                        $json['filepath'] = $file_path;
                    } else {
                        $json['message'] = "Server error";
                    }
                } else {
                    $json['message'] = "No file path";
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