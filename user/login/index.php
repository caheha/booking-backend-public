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
        $password = isset($_REQUEST['password']) ? $_REQUEST['password'] : null;

        // Check if all values are provided
        if ($email && $password) {

            $response = json_decode($mySQL->Query("SELECT email, hashed_password, id FROM booking_userLogin WHERE email = '$email' LIMIT 1"));

            // Check if email matches
            if (count($response->data)) {
                $verify = password_verify($password, $response->data[0]->hashed_password);

                // Check if password matches
                if ($verify) {
                    $id =  $response->data[0]->id;

                    // Return userdata in respoesn
                    $userProfile = json_decode($mySQL->Query("SELECT firstname, lastname, phone, avatar FROM booking_userProfile WHERE id = '$id' LIMIT 1"));

                    $json['status'] = 'success';
                    $json['message'] = 'User logged in';
                    $json['token'] = $jwt->generate(['id' => $id]);
                    $json['id'] = $id;
                    $json['firstname'] = $userProfile->data[0]->firstname;
                    $json['lastname'] = $userProfile->data[0]->lastname;
                    $json['phone'] = $userProfile->data[0]->phone;
                    $json['avatar'] = $userProfile->data[0]->avatar;
                    $json['email'] = $response->data[0]->email;
                } else {
                    $json['message'] = 'Login credentials are invalid';
                }
            } else {
                $json['message'] = 'User with email does not exist';
            }
        } else {
            $json['message'] = 'Inputs not filled in';
        }
    } else {
        $json['message'] = 'No request';
    }
    
    echo json_encode($json);
?>