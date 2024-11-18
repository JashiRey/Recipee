<?php
  require "../../start.php";

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate required fields
    if (!isset($data['email']) || !isset($data['password'])) {
        http_response_code(400);
        echo json_encode([
            "message" => "Email and password are required"
        ], JSON_PRETTY_PRINT);
        die();
    }

    // Get user by email
    $query = "SELECT id, name, email, password FROM users WHERE email = ?";
    $stmt = mysqli_prepare($db, $query);
    mysqli_stmt_bind_param($stmt, "s", $data['email']);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    // Check if user exists and verify password
    if ($user && password_verify($data['password'], $user['password'])) {
        // Remove password from response
        unset($user['password']);
        
        http_response_code(200);
        echo json_encode([
            "message" => "Authentication successful",
            "user" => $user
        ], JSON_PRETTY_PRINT);
        die();
    }

    // Invalid credentials
    http_response_code(401);
    echo json_encode([
        "message" => "Invalid email or password"
    ], JSON_PRETTY_PRINT);
    die();
  }

  http_response_code(405);
  echo json_encode(["message" => "Invalid request method"], JSON_PRETTY_PRINT);


  require "../end.php";