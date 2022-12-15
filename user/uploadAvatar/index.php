<?php 
    header('Content-Type: application/json');

    
    $json = [];
    $json["status"] = "error";

    // If no file was uploaded, exit
    if (empty($_FILES)) {
        $json['message'] = "No request";
        exit(0);
    }
    
    // Import
    include('../../mySQL.php');
    include('../../JWT.php');

    // Globals
    global $mySQL;
    global $jwt;
    global $token;

    // Validate token
    if ($jwt->validate($token)) {
        // Get id and image from request
        $id = isset($jwt->decode($token)->id) ? $jwt->decode($token)->id : null;
        $image = isset($_FILES['image']) ? $_FILES['image'] : null;

        if ($id && $image) {
            // Check if user exists
            $response = json_decode($mySQL->Query("SELECT id, avatar FROM booking_userProfile WHERE id = '$id' LIMIT 1"));
    
            // If user exists
            if (count($response->data)) {
                // If an avatar already exists, remove it
                if ($response->data[0]->avatar) {
                    $file_path = '../../' . $response->data[0]->avatar;
                    if (file_exists($file_path)) {
                        unlink($file_path);
                    }
                }

                // Allowed filetypes
                $fileType = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
                $allowedFiletypes = ["jpg", "jpeg", "gif", "png"];
        
                // Check if uploaded image is allowed
                if (in_array($fileType, $allowedFiletypes) && $image["size"] < 10000000) {
                    // Upload image and update table with filepath
                    $json["status"] = "success";
                    $targetFolder = "../../images/avatars/";
                    $fileName = 'avatar_' . $id . '_' . date('U') .  '.' . $fileType;
                    move_uploaded_file($image["tmp_name"], $targetFolder . $fileName);
        
                    $fileDestination = '/images/avatars/' . $fileName;
        
                    $sql = "UPDATE booking_userProfile SET avatar = '$fileDestination' WHERE id = '$id'";
                    $result = $mySQL->Query($sql, false);
        
                    if ($result) {
                        $json['status'] = 'success';
                        $json['message'] = "Avatar uploaded successfully";
                        $json['avatar'] = $fileDestination;
                    } else {
                        $json['message'] = "Server error";
                    }
                } else {
                    $json['message'] = "Wrong filetype or too large";
                } 
            } else {
                $json['message'] = "No user exists";
            }
        } else { 
            $json['message'] = "Manglende argumenter";
        }  
    } else {
        $json['message'] = "Invalid token";
    } 

    echo json_encode($json);
?>