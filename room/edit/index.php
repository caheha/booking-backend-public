<?php
    header('Content-Type: application/json');

    $json = [];
    $json['status'] = "error";

    // Check if request is empty
    if(!empty($_REQUEST)) {
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
            $roomId = isset($_REQUEST['id']) ? $_REQUEST['id'] : null;
            $description = isset($_REQUEST['description']) ? $_REQUEST['description'] : null;
            $address = isset($_REQUEST['address']) ? $_REQUEST['address'] : null;
            $zipcode = isset($_REQUEST['zipcode']) ? $_REQUEST['zipcode'] : null;
            $city = isset($_REQUEST['city']) ? $_REQUEST['city'] : null;
            $area = isset($_REQUEST['area']) ? $_REQUEST['area'] : null;
            $price = isset($_REQUEST['price']) ? $_REQUEST['price'] : null;
            $timeUnit = isset($_REQUEST['timeUnit']) ? $_REQUEST['timeUnit'] : null;
            $startTime = isset($_REQUEST['startTime']) ? $_REQUEST['startTime'] : null;
            $endTime = isset($_REQUEST['endTime']) ? $_REQUEST['endTime'] : null;
            $visible = isset($_REQUEST['visible']) ? $_REQUEST['visible'] : null;
            $image = isset($_FILES['image']) ? $_FILES['image'] : null;
            $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
            $facilities = isset($_REQUEST['facilities']) ? $_REQUEST['facilities'] : null;

            // Check if all values are provided
            if($id && $roomId && $description && $address && $zipcode && $city && $area && $price && $timeUnit && $startTime && $endTime && $visible) {
                // Check if user owns the room
                $sql = "SELECT id, thumbnail from booking_RoomListing WHERE roomId = '$roomId'";
                $res = json_decode($mySQL->Query($sql));

                if (count($res->data)) {
                    $userId = $res->data[0]->id;
        
                    if ($userId === $id) {
                        // Call edit room procedure
                        $response = $mySQL->Query("CALL edit_room('$roomId','$description','$address','$zipcode','$city','$area','$price','$timeUnit', '$startTime', '$endTime', '$visible')", false);
                        
                        // Check if procedure was successful
                        if ($response){   
                            // If an image was provided, remove old image and update field with new file path                       
                            if ($image) {
                                $fileType = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
                                $allowedFiletypes = ["jpg", "jpeg", "gif", "png", "webp"];
                                
                                if (in_array($fileType, $allowedFiletypes) && $image["size"] < 10000000) {
                                    //Check if room has an image and remove it
                                    if ($res->data[0]->thumbnail) {
                                        $file_path = '../../' . $res->data[0]->thumbnail;
                                        if (file_exists($file_path)) {
                                            unlink($file_path);
                                        }
                                    }

                                    //Upload and update img
                                    $targetFolder = "../../images/rooms/";
                                    $fileName = 'rooms_' . date('U') .  '.' . $fileType;
                                    move_uploaded_file($image["tmp_name"], $targetFolder . $fileName);
                        
                                    $imagepath= '/images/rooms/' . $fileName;

                                    $sql = "UPDATE booking_RoomListing SET thumbnail = '$imagepath' WHERE roomId = '$roomId'";
                                    $mySQL->Query($sql, false);
                                }
                            }

                            // Delete and add categories again
                            $deleteCatsql = "DELETE FROM booking_RoomCategories WHERE roomId = '$roomId'";
                            $mySQL->Query($deleteCatsql, false);
                            if($type){
                                $type_arr = explode(',', $type);
                                foreach($type_arr as $catId){
                                    $category_res = $mySQL->Query("CALL booking_CreateRoomCategory('$roomId', '$catId')", false);
                                }
                            }
                            if($facilities){
                                $facilities_arr = explode(',', $facilities);
                                foreach($facilities_arr as $catId){
                                    $category_res = $mySQL->Query("CALL booking_CreateRoomCategory('$roomId', '$catId')", false);
                                }
                            }

                            $json['status'] = "success";
                            $json['message'] = "Room details has been changed";
                        } else {
                            $json['message'] = "Room details has not been changed";
                        }
                    } else {
                        $json['message'] = "User does not own this room";
                    }
                } else {
                    $json['message'] = "Room does not exist";
                }
            } else {
                $json['message'] = "Misisng arguments";
            }
        } else {
            $json['message'] = "Invalid token";
        }
    } else {
        $json['message'] = "No request";
    }

    echo json_encode($json);
?>

