<?php
    header('Content-Type: application/json');

    $json = [];
    $json["status"] = "error";

    // Import
    include('../../mySQL.php');

    // Globals
    global $mySQL;

    // Filters
    $search = isset($_REQUEST['search']) ? $_REQUEST['search'] : null;
    $type = isset($_REQUEST['type']) && $_REQUEST['type'] ? $_REQUEST['type'] : [];
    $facilities = isset($_REQUEST['facilities']) && $_REQUEST['facilities'] ? explode(',', $_REQUEST['facilities']) : [];
    $order = isset($_REQUEST['order']) ? $_REQUEST['order'] : 'createdAt DESC';

    // Base SQL query
    $sql = "SELECT * FROM booking_RoomListing";

    // Store filter options
    $where = [];

    // If search query is provided, match with roomdescription, city, zipcode, or address 
    if ($search) {
        $where[] = "(roomDescription LIKE '%$search%' OR city LIKE '%$search%' OR zipcode LIKE '%$search%' OR roomAddress LIKE '%$search%')";
    }

    // If room types are provided, match with category IDs
    if ($type){
        $where[] = "roomId = ANY (SELECT roomId FROM booking_RoomCategories WHERE categoryId IN ($type))";
    }

    // If room facilitites are provided, match for every facility
    if ($facilities){
        foreach ($facilities as $catId){
            $where[] = "roomId = ANY (SELECT roomId FROM booking_RoomCategories WHERE categoryId IN ($catId))";
        }
    }
    
    // Implode filter options and add them to base string
    if ($search || $facilities || $type){
        $where = " WHERE ".implode(' AND ', $where);
        $sql .= $where;
    }

    // If order is proivded, add it to base string
    if ($order){ 
        $sql .= " ORDER BY $order"; 
    }
    
    $res = json_decode($mySQL->Query($sql));

    if($res){
        // If any rooms matches the query
        if (count($res->data)) {
            // Add categories to room posts
            foreach ($res->data as $data){
                $cat_sql = "SELECT * FROM booking_RoomCategories
                            INNER JOIN booking_Categories  
                            ON booking_RoomCategories.categoryId = booking_Categories.id
                            HAVING booking_RoomCategories.roomId = $data->roomId";

                $categories = json_decode($mySQL->Query($cat_sql));
                
                $type = array_values(array_filter($categories->data, function($obj){ 
                    return $obj->categoryType === 'type';
                }));

                $facilities = array_values(array_filter($categories->data, function($obj){ 
                    return $obj->categoryType === 'facilities';
                }));

                $data->categories = [
                    'type' => $type,
                    'facilities' => $facilities
                ];
            }

            $json["status"] = "success";
            $json["rooms"] = $res->data;
        } else {
            $json["message"] = "No rooms found";
        }
    }
    
    echo json_encode($json);
?>