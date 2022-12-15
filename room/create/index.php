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
            $description = isset($_REQUEST['description']) ? $_REQUEST['description'] : null;
            $address = isset($_REQUEST['address']) ? $_REQUEST['address'] : null;
            $zipcode = isset($_REQUEST['zipcode']) ? $_REQUEST['zipcode'] : null;
            $city = isset($_REQUEST['city']) ? $_REQUEST['city'] : null;
            $area = isset($_REQUEST['area']) ? $_REQUEST['area'] : null;
            $price = isset($_REQUEST['price']) ? $_REQUEST['price'] : null;
            $timeUnit = isset($_REQUEST['timeUnit']) ? $_REQUEST['timeUnit'] : null;
            $startTime = isset($_REQUEST['startTime']) ? $_REQUEST['startTime'] : null;
            $endTime = isset($_REQUEST['endTime']) ? $_REQUEST['endTime'] : null;
            $image = isset($_FILES['image']) ? $_FILES['image'] : null;
            $type = isset($_REQUEST['type']) ? $_REQUEST['type'] : null;
            $facilities = isset($_REQUEST['facilities']) ? $_REQUEST['facilities'] : null;

            // Check if all values are provided
            if ($id && $description && $address && $zipcode && $city && $area && $price && $timeUnit && $startTime && $endTime) {
                
                $response = json_decode($mySQL->Query("SELECT id FROM booking_userLogin WHERE id = '$id' LIMIT 1"));
                
                // Check if user exists
                if (count($response->data)) {
                    $imagepath = null;

                    // If an image was provided while creating room, upload it, and add file path to field
                    if ($image) {
                        $fileType = strtolower(pathinfo($image["name"], PATHINFO_EXTENSION));
        
                        $allowedFiletypes = ["jpg", "jpeg", "gif", "png", "webp"];
                
                        if (in_array($fileType, $allowedFiletypes) && $image["size"] < 10000000) {
                            $json["status"] = "success";
                            $targetFolder = "../../images/rooms/";
                            $fileName = 'rooms_' . date('U') .  '.' . $fileType;
                            move_uploaded_file($image["tmp_name"], $targetFolder . $fileName);
                
                            $imagepath= '/images/rooms/' . $fileName;
                        }
                    }

                    // Call create room procedure
                    $res = $mySQL->Query("CALL booking_CreateRoom('$id', '$description', '$address', '$zipcode', '$city', '$area', '$price', '$timeUnit', '$startTime', '$endTime', '$imagepath')", false);

                    // Get id from created room to add categories
                    $roomCreated = json_decode($mySQL->Query("SELECT roomId FROM booking_RoomListing WHERE id = '$id' ORDER BY roomId DESC LIMIT 1"));
                    $roomId = $roomCreated->data[0]->roomId;
                    
                    //Add room categories
                    if($roomId){
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
                    }

                    if ($res) {
                        $json['status'] = 'success';
                        $json['message'] = 'Lokale oprettet';
                        $bookingNote = $mySQL->Query("INSERT INTO booking_notifications (userId, title, note, noticeType) VALUES('$id','Lokale oprettet', 'Dit lokale kan nu udlejes til andre', 'success')", false);
                    } else {
                        $json['message'] = 'Server error';
                    }
                } else {
                    $json['message'] = 'User not valid';
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