<?php

  error_reporting(E_ALL);
  ini_set('display_errors', 1);
    
  header("Content-Type: application/json"); // Asegura que la respuesta sea JSON

  $db = new mysqli('localhost', 'root', '', 'recipee');

  if(!$db) {
    die('Connection failed: ' . mysqli_connect_error());
  }
