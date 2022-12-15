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

        $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
        $password = isset($_REQUEST['password']) ? password_hash($_REQUEST['password'], PASSWORD_DEFAULT) : null;
        $firstname = isset($_REQUEST['firstname']) ? $_REQUEST['firstname'] : null;
        $lastname = isset($_REQUEST['lastname']) ? $_REQUEST['lastname'] : null;
        $phone = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : null;

        // Check if all values are provided
        if ($email && $password && $firstname && $lastname && $phone) {
           
            $response = json_decode($mySQL->Query("SELECT email FROM booking_userLogin WHERE email = '$email' LIMIT 1"));

            // Check If email already exists, proceed if it doesn't
            if (!count($response->data)) {
                // Create new user and add user information to the response
                $response = $mySQL->Query("CALL booking_CreateUser('$firstname', '$lastname', '$phone', '$email', '$password')", false);
                
                $res = json_decode($mySQL->Query("SELECT id, email FROM booking_userLogin WHERE email = '$email' LIMIT 1"));

                $id = $res->data[0]->id;

                $userProfile = json_decode($mySQL->Query("SELECT firstname, lastname, phone, avatar FROM booking_userProfile WHERE id = '$id' LIMIT 1"));

                if ($response) {
                    $json['status'] = 'success';
                    $json['message'] = 'User created';
                    $json['id'] = $id;
                    $json['token'] = $jwt->generate(['id' => $id]);
                    $json['firstname'] = $userProfile->data[0]->firstname;
                    $json['lastname'] = $userProfile->data[0]->lastname;
                    $json['phone'] = $userProfile->data[0]->phone;
                    $json['avatar'] = $userProfile->data[0]->avatar;
                    $json['email'] = $res->data[0]->email;
                    $welcomeNote = $mySQL->Query("INSERT INTO booking_notifications (userId, title, note, noticeType) VALUES('$id','Velkommen', 'Velkommen $firstname', 'success')", false);
                    $json['welcome'] = $welcomeNote;
                } else {
                    $json['message'] = 'Server error';
                }
            } else {
                $json['message'] = 'User with email already exists';
            }
        } else {
            $json['message'] = 'Inputs not filled in';
        }
    } else {
        $json['message'] = 'No request';
    }
    
    echo json_encode($json);
?>