<?php
    header('Content-Type: application/json');

    $json = [];
    $json['status'] = 'error';

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

        // Check if id is provided
        if ($id) {
            $userLogin = json_decode($mySQL->Query("SELECT email FROM booking_userLogin WHERE id = '$id' LIMIT 1"));

            // Check if user exists
            if (count($userLogin->data)) {
                
                $userProfile = json_decode($mySQL->Query("SELECT firstname, lastname, phone, avatar FROM booking_userProfile WHERE id = '$id' LIMIT 1"));
                // Get user data and add it to response
                if (count($userProfile->data)) {
                    $json['id'] = $id;
                    $json['firstname'] = $userProfile->data[0]->firstname;
                    $json['lastname'] = $userProfile->data[0]->lastname;
                    $json['phone'] = $userProfile->data[0]->phone;
                    $json['avatar'] = $userProfile->data[0]->avatar;
                    $json['email'] = $userLogin->data[0]->email;
                } else {
                    $json['message'] = 'Server error';
                }
            } else {
                $json['message'] = 'User with email already exists';
            }
        } else {
            $json['message'] = 'Inputs not filled in';
        }
    } 
    
    echo json_encode($json);
?>