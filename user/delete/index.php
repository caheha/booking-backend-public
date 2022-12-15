<?php
    include('../../headers.php');

    $json = [];
    $json['status'] = "error";

    // Import
    include('../../mySQL.php');
    include('../../JWT.php');

    global $mySQL;
    global $jwt;
    global $token;

    // Validate token
    if ($jwt->validate($token)) {
        $id = isset($jwt->decode($token)->id) ? $jwt->decode($token)->id : null;

        if ($id) {
            // Call delete user procedure
            $response = $mySQL->Query("CALL delete_user('$id')", false);

            if ($response){
                $json['status'] = "succes";
                $json['message'] = "succesfully deleted user";
            } else {
                $json['message'] = "Something went wrong, user not deleted";
            }
        } else {
            $json['message'] = "No matching user";
        }
    } else {
        $json['message'] = "Invalid token";
    }
    
    echo json_encode($json);
?>