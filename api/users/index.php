<?php
  require "../start.php";
  
  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = $_GET["id"] ?? null;
    $email = $_GET["email"] ?? null;

    $conditions = [];
    $params = [];
    $types = "";

    // Build query conditions
    if ($id !== null) {
        $conditions[] = "id = ?";
        $params[] = $id;
        $types .= "i";
    }
    if ($email !== null) {
        $conditions[] = "email = ?";
        $params[] = $email;
        $types .= "s";
    }

    // Base query
    $query = "SELECT id, name, email FROM users"; // Excluding password from select
    
    // Add WHERE clause if there are conditions
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    // Prepare and execute the statement
    $stmt = mysqli_prepare($db, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);

    echo json_encode($users, JSON_PRETTY_PRINT);
    die();
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validate required fields
    if (!isset($data['name']) || !isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields"], JSON_PRETTY_PRINT);
        die();
    }

    // Check if email already exists
    $check_stmt = mysqli_prepare($db, "SELECT id FROM users WHERE email = ?");
    mysqli_stmt_bind_param($check_stmt, "s", $data['email']);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);
    
    if (mysqli_num_rows($result) > 0) {
        http_response_code(409);
        echo json_encode("Email already exists", JSON_PRETTY_PRINT);
        die();
    }

    // Hash password
    $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);

    // Insert user
    $query = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "sss", 
        $data['name'],
        $data['email'],
        $hashed_password
    );

    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to create user"], JSON_PRETTY_PRINT);
        die();
    }

    $user_id = mysqli_insert_id($db);

    http_response_code(201);
    echo json_encode([
        "message" => "User created successfully",
        "user_id" => $user_id
    ], JSON_PRETTY_PRINT);
    die();
  }

  if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate user ID
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "User ID is required"], JSON_PRETTY_PRINT);
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
    if (isset($data['email'])) {
        // Check if new email already exists for different user
        $check_stmt = mysqli_prepare($db, "SELECT id FROM users WHERE email = ? AND id != ?");
        mysqli_stmt_bind_param($check_stmt, "si", $data['email'], $data['id']);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        
        if (mysqli_num_rows($result) > 0) {
            http_response_code(400);
            echo json_encode(["message" => "Email already exists"], JSON_PRETTY_PRINT);
            die();
        }

        $updates[] = "email = ?";
        $params[] = $data['email'];
        $types .= "s";
    }
    if (isset($data['password'])) {
        $updates[] = "password = ?";
        $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        $types .= "s";
    }

    // Only update if there are fields to update
    if (!empty($updates)) {
        $query = "UPDATE users SET " . implode(", ", $updates) . " WHERE id = ?";
        $params[] = $data['id'];
        $types .= "i";

        $stmt = mysqli_prepare($db, $query);
        mysqli_stmt_bind_param($stmt, $types, ...$params);
        
        if (!mysqli_stmt_execute($stmt)) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update user"], JSON_PRETTY_PRINT);
            die();
        }

        echo json_encode([
            "message" => "User updated successfully",
            "user_id" => $data['id']
        ], JSON_PRETTY_PRINT);
        die();
    }
  }

  if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    // Get user ID from query parameters
    $id = $_GET["id"] ?? null;

    // Validate user ID
    if ($id === null) {
        http_response_code(400);
        echo json_encode(["message" => "User ID is required"], JSON_PRETTY_PRINT);
        die();
    }

    // Check if user exists
    $check_stmt = mysqli_prepare($db, "SELECT id FROM users WHERE id = ?");
    mysqli_stmt_bind_param($check_stmt, "i", $id);
    mysqli_stmt_execute($check_stmt);
    $result = mysqli_stmt_get_result($check_stmt);

    if (mysqli_num_rows($result) === 0) {
        http_response_code(404);
        echo json_encode(["message" => "User not found"], JSON_PRETTY_PRINT);
        die();
    }

    // Delete user
    $delete_stmt = mysqli_prepare($db, "DELETE FROM users WHERE id = ?");
    mysqli_stmt_bind_param($delete_stmt, "i", $id);
    
    if (!mysqli_stmt_execute($delete_stmt)) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to delete user"], JSON_PRETTY_PRINT);
        die();
    }

    echo json_encode([
        "message" => "User deleted successfully",
        "user_id" => $id
    ], JSON_PRETTY_PRINT);
    die();
  }

  http_response_code(405);
  echo json_encode(["message" => "Invalid request method"], JSON_PRETTY_PRINT);


  require "../end.php";