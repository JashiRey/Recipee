<?php
  require "../start.php";
  
  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = $_GET["id"] ?? null;
    $name = $_GET["name"] ?? null;

    $conditions = [];
    $params = [];
    $types = "";

    // Build query conditions
    if ($id !== null) {
        $conditions[] = "id = ?";
        $params[] = $id;
        $types .= "i";
    }
    if ($name !== null) {
        $conditions[] = "name LIKE ?";
        $params[] = "%$name%";
        $types .= "s";
    }

    // Base query
    $query = "SELECT id, name FROM ingredients";
    
    // Add WHERE clause if there are conditions
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    // Add ORDER BY name
    $query .= " ORDER BY name ASC";

    // Prepare and execute the statement
    $stmt = mysqli_prepare($db, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ingredients = mysqli_fetch_all($result, MYSQLI_ASSOC);

    echo json_encode($ingredients, JSON_PRETTY_PRINT);
    die();
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validate required fields
    if (!isset($data['name'])) {
        http_response_code(400);
        echo json_encode(["message" => "Name is required"], JSON_PRETTY_PRINT);
        die();
    }

    // Check if name already exists
    $check_stmt = mysqli_prepare($db, "SELECT id FROM ingredients WHERE name = ?");
    mysqli_stmt_bind_param($check_stmt, "s", $data['name']);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($result) > 0) {
        http_response_code(400);
        echo json_encode(["message" => "Name already exists"], JSON_PRETTY_PRINT);
        die();
    }

    // Insert ingredient
    $query = "INSERT INTO ingredients (name) VALUES (?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", 
        $data['name']
    );

    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to create ingredient"], JSON_PRETTY_PRINT);
        die();
    }

    $ingredient_id = mysqli_insert_id($db);

    http_response_code(201);
    echo json_encode([
        "message" => "Ingredient created successfully",
        "ingredient_id" => $ingredient_id
    ], JSON_PRETTY_PRINT);
    die();
  }

  if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate ingredient ID
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "Ingredient ID is required"], JSON_PRETTY_PRINT);
        die();
    }

    // Build update query dynamically based on provided fields
    $updates = [];
    $params = [];
    $types = "";

    // Check each updatable field
    if (isset($data['name'])) {
        $updates[] = "name = ?";
        $params[] = $data['name'];
        $types .= "s";
    }

    // Only update if there are fields to update
    if (!empty($updates)) {
        $query = "UPDATE ingredients SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $data['id'];
        $types .= "i";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        
        if (!mysqli_stmt_execute($stmt)) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update ingredient"], JSON_PRETTY_PRINT);
            die();
        }

        echo json_encode([
            "message" => "Ingredient updated successfully",
            "ingredient_id" => $data['id']
        ], JSON_PRETTY_PRINT);
        die();
    }
  }

  if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    // Get ingredient ID from query parameters
    $id = $_GET["id"] ?? null;

    // Validate ingredient ID
    if ($id === null) {
        http_response_code(400);
        echo json_encode(["message" => "Ingredient ID is required"], JSON_PRETTY_PRINT);
        die();
    }

    // Check if ingredient exists
    $check_stmt = mysqli_prepare($db, "SELECT id FROM ingredients WHERE id = ?");
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($result) === 0) {
        http_response_code(404);
        echo json_encode(["message" => "Ingredient not found"], JSON_PRETTY_PRINT);
        die();
    }

    // Delete ingredient
    $delete_stmt = mysqli_prepare($db, "DELETE FROM ingredients WHERE id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $id);
    
    if (!mysqli_stmt_execute($delete_stmt)) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to delete ingredient"], JSON_PRETTY_PRINT);
        die();
    }

    echo json_encode([
        "message" => "Ingredient deleted successfully",
        "ingredient_id" => $id
    ], JSON_PRETTY_PRINT);
    die();
  }

  http_response_code(405);
  echo json_encode(["message" => "Invalid request method"], JSON_PRETTY_PRINT);


  require "../end.php";