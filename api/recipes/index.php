<?php
  require "../start.php";

  
  // id, user_id, name, content, imgUrl, (ingredient_ids)
  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = $_GET["id"];
    $user_id = $_GET["user_id"];
    $name = $_GET["name"];
    $ingredient_ids = $_GET["ingredient_ids"];

    $debug = ["id" => $id, "user_id" => $user_id, "name" => $name,"ingredient_ids" => $ingredient_ids];

    print_r(json_encode($debug, JSON_PRETTY_PRINT));

    // TODO: build query
    $query = 'SELECT * FROM recipes';
  
    $result = mysqli_query($db, $query);
  
    $recipes = mysqli_fetch_all($result, MYSQLI_ASSOC);
  
    echo json_encode($recipes, JSON_PRETTY_PRINT);
    die();
  }

  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // TODO: add method
  }

  if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    // TODO: add method
  }

  if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    // TODO: add method
  }

  http_response_code(405);
  echo json_encode(["message" => "Invalid request method"], JSON_PRETTY_PRINT);


  require "../end.php";