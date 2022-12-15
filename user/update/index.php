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
    
        // Validate token
        if ($jwt->validate($token)) {
            $id = isset($jwt->decode($token)->id) ? $jwt->decode($token)->id : null;
            $firstname = isset($_REQUEST['firstname']) ? $_REQUEST['firstname'] : null;
            $lastname = isset($_REQUEST['lastname']) ? $_REQUEST['lastname'] : null;
            $phoneNum = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : null;
            $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : null;
    
            // Check all values are entered
            if ($id && $firstname && $lastname && $phoneNum && $email) {
                $response = $mySQL->Query("CALL edit_user('$id','$firstname','$lastname','$phoneNum','$email')", false);
    
                // See if request was successful
                if ($response) {
                  $json['status'] = "success";
                  $json['message'] = "Your profile details has been changed";
                } else {
                  $json['message'] = "Could not find user";
                }
            } else {
              $json['message'] = "Manglende argumenter";
            }
        } else {
            $json['message'] = "Invalid token";
        }
    } else {
        $json['message'] = "No request";
    }

    echo json_encode($json);
?>