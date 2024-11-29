<?php

  error_reporting(E_ALL);
  ini_set('display_errors', 1);
    
  header("Content-Type: application/json"); // Asegura que la respuesta sea JSON

  try {
    $db = new mysqli('localhost', 'root', '', 'recipee');
    // echo "Conexión exitosa";
  } catch (Exception $e) {
      // echo "Error: " . $e->getMessage();
      http_response_code(500);
      echo json_encode(["message" => "Failed to connect to database"], JSON_PRETTY_PRINT);
      die();
  }

  if(!$db) {
    // die('Connection failed: ' . mysqli_connect_error());
    http_response_code(500);
    echo json_encode(["message" => "Failed to connect to database"], JSON_PRETTY_PRINT);
    die();
  }
