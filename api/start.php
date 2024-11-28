<?php

  error_reporting(E_ALL);
  ini_set('display_errors', 1);
    
  header("Content-Type: application/json"); // Asegura que la respuesta sea JSON

<<<<<<< Updated upstream
  $db = new mysqli('localhost', 'root', '', 'recipee');
=======
  try {
    $db = new mysqli('localhost', 'root', '', 'recipee');
    echo "Conexión exitosa";
  } catch (Exception $e) {
      echo "Error: " . $e->getMessage();
  }
>>>>>>> Stashed changes

  if(!$db) {
    die('Connection failed: ' . mysqli_connect_error());
  }
