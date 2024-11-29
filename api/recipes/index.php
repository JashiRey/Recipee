<?php
  require "../start.php";
  
  // id, user_id, name, content, imgUrl, (ingredient_ids)
  if ($_SERVER["REQUEST_METHOD"] == "GET") {
    $id = $_GET["id"] ?? null;
    $user_id = $_GET["user_id"] ?? null;
    $name = $_GET["name"] ?? null;
    $ingredient_ids = $_GET["ingredient_ids"] ?? null;
    $search = $_GET["search"] ?? null;

    $conditions = [];
    $params = [];
    $types = "";

    // Build query conditions
    if ($id && $id !== null) {
        $conditions[] = "r.id = ?";
        $params[] = $id;
        $types .= "i";
    }
    if ($user_id && $user_id !== null) {
        $conditions[] = "r.user_id = ?";
        $params[] = $user_id;
        $types .= "i";
    }
    if ($name && $name !== null) {
        $conditions[] = "r.name LIKE ?";
        $params[] = "%$name%";
        $types .= "s";
    }
    if ($search && $search !== null) {
        $conditions[] = "LOWER(r.name) LIKE LOWER(?) OR LOWER(r.content) LIKE LOWER(?)";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= "ss";
    }

    // Base query
    $query = "SELECT DISTINCT 
        r.*,
        GROUP_CONCAT(
            CONCAT(
                '{',
                '\"name\":\"', i.name, '\",',
                '\"quantity\":', rxi.quantity, ',',
                '\"unit\":\"', rxi.unit, '\",',
                '\"format\":\"', rxi.format, '\"',
                '}'
            )
        ) as ingredients
    FROM recipes r
    LEFT JOIN recipes_x_ingredients rxi ON r.id = rxi.recipe_id
    LEFT JOIN ingredients i ON rxi.ingredient_id = i.id";
    
    // Add ingredient_ids join if provided
    if ($ingredient_ids && $ingredient_ids !== null) {
        $ingredient_ids_array = explode(',', $ingredient_ids);
        $query .= " JOIN recipes_x_ingredients rxi2 ON r.id = rxi2.recipe_id";
        $conditions[] = "rxi2.ingredient_id IN (" . str_repeat('?,', count($ingredient_ids_array) - 1) . "?)";
        $params = array_merge($params, $ingredient_ids_array);
        $types .= str_repeat("i", count($ingredient_ids_array));
    }

    // Add WHERE clause if there are conditions
    if (!empty($conditions)) {
        $query .= " WHERE " . implode(" AND ", $conditions);
    }

    // Add GROUP BY to handle the JSON_ARRAYAGG
    $query .= " GROUP BY r.id";

    // Add ORDER BY to sort by relevance if searching
    if ($search !== null) {
        $query .= " ORDER BY 
            CASE 
                WHEN LOWER(r.name) LIKE LOWER(?) THEN 1
                WHEN LOWER(r.content) LIKE LOWER(?) THEN 2
                ELSE 3 
            END";
        $params[] = "%$search%";
        $params[] = "%$search%";
        $types .= "ss";
    }

    // Prepare and execute the statement
    $stmt = mysqli_prepare($db, $query);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt, $types, ...$params);
    }
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $recipes = mysqli_fetch_all($result, MYSQLI_ASSOC);

    // Parse the ingredients string into an array
    foreach ($recipes as &$recipe) {
        if ($recipe['ingredients'] !== null) {
            // Envolver el string en corchetes para convertirlo en un array JSON válido
            $jsonString = '[' . $recipe['ingredients'] . ']';
            $recipe['ingredients'] = json_decode($jsonString, true);
        } else {
            $recipe['ingredients'] = [];
        }
    }

    unset($recipe); // Limpiar la referencia

    echo json_encode($recipes, JSON_PRETTY_PRINT);
    die();
  }

  // Create a new recipe
  if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    // Validate required fields
    if (!isset($data['user_id']) || !isset($data['name']) || !isset($data['content']) || !isset($data['imgurl'])) {
        http_response_code(400);
        echo json_encode(["message" => "Missing required fields"], JSON_PRETTY_PRINT);
        die();
    }

    // Insert recipe
    $query = "INSERT INTO recipes (user_id, name, content, imgurl) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($db, $query);
    
    // Store values in variables first
    $user_id = $data['user_id'];
    $name = $data['name'];
    $content = $data['content'];
    $imgurl = $data['imgurl'];
    
    // Bind parameters using variables
    mysqli_stmt_bind_param($stmt, "isss", $user_id, $name, $content, $imgurl);

    if (!mysqli_stmt_execute($stmt)) {
        http_response_code(500);
        echo json_encode(["message" => "Failed to create recipe"], JSON_PRETTY_PRINT);
        die();
    }

    $recipe_id = mysqli_insert_id($db);

    // Insert ingredients if provided
    if (isset($data['ingredients']) && is_array($data['ingredients'])) {
        $ingredient_query = "INSERT INTO recipes_x_ingredients (recipe_id, ingredient_id, quantity, unit, format) VALUES (?, ?, ?, ?, ?)";
        $ingredient_stmt = mysqli_prepare($db, $ingredient_query);

        foreach ($data['ingredients'] as $ingredient) {
            if (!isset($ingredient['ingredient_id']) || !isset($ingredient['quantity']) || !isset($ingredient['unit'])) {
                continue;
            }

            // Store ingredient values in variables
            $ingredient_id = $ingredient['ingredient_id'];
            $quantity = $ingredient['quantity'];
            $unit = $ingredient['unit'];
            $format = $ingredient['format'] ?? 'decimal';

            // Bind parameters using variables
            mysqli_stmt_bind_param($ingredient_stmt, "iidss",
                $recipe_id,
                $ingredient_id,
                $quantity,
                $unit,
                $format
            );
            mysqli_stmt_execute($ingredient_stmt);
        }
    }

    // Return the created recipe
    http_response_code(201);
    echo json_encode([
        "message" => "Recipe created successfully",
        "recipe_id" => $recipe_id
    ], JSON_PRETTY_PRINT);
    die();
  }

  // Update a recipe
  if ($_SERVER["REQUEST_METHOD"] == "PUT") {
    // Get JSON data from request body
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    // Validate recipe ID
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "Recipe ID is required"], JSON_PRETTY_PRINT);
        die();
    }

    // Start transaction
    mysqli_begin_transaction($db);
    try {
        // Build update query dynamically based on provided fields
        $updates = [];
        $bind_params = [];
        $types = "";

        // Check each updatable field
        if (isset($data['name'])) {
            $updates[] = "name = ?";
            $bind_params[] = $data['name'];
            $types .= "s";
        }
        if (isset($data['content'])) {
            $updates[] = "content = ?";
            $bind_params[] = $data['content'];
            $types .= "s";
        }
        if (isset($data['imgurl'])) {
            $updates[] = "imgurl = ?";
            $bind_params[] = $data['imgurl'];
            $types .= "s";
        }

        // Only update if there are fields to update
        if (!empty($updates)) {
            $query = "UPDATE recipes SET " . implode(", ", $updates) . " WHERE id = ?";
            $bind_params[] = $data['id'];
            $types .= "i";

            $stmt = mysqli_prepare($db, $query);
            
            // Create references array for bind_param
            $refs = [];
            foreach($bind_params as $key => $value) {
                $refs[$key] = &$bind_params[$key];
            }
            
            // Bind parameters
            mysqli_stmt_bind_param($stmt, $types, ...$refs);
            mysqli_stmt_execute($stmt);
        }

        // Update ingredients if provided
        if (isset($data['ingredients']) && is_array($data['ingredients'])) {
            // First, delete existing ingredients
            $delete_stmt = mysqli_prepare($db, "DELETE FROM recipes_x_ingredients WHERE recipe_id = ?");
            mysqli_stmt_bind_param($delete_stmt, "i", $data['id']);
            mysqli_stmt_execute($delete_stmt);

            // Then insert new ingredients
            $ingredient_query = "INSERT INTO recipes_x_ingredients (recipe_id, ingredient_id, quantity, unit, format) VALUES (?, ?, ?, ?, ?)";
            $ingredient_stmt = mysqli_prepare($db, $ingredient_query);

            foreach ($data['ingredients'] as $ingredient) {
                if (!isset($ingredient['ingredient_id']) || !isset($ingredient['quantity']) || !isset($ingredient['unit'])) {
                    continue;
                }

                // Create references for bind_param
                $recipe_id = $data['id'];
                $ingredient_id = $ingredient['ingredient_id'];
                $quantity = $ingredient['quantity'];
                $unit = $ingredient['unit'];
                $format = $ingredient['format'] ?? 'decimal';

                mysqli_stmt_bind_param($ingredient_stmt, "iidss", 
                    $recipe_id, 
                    $ingredient_id, 
                    $quantity, 
                    $unit, 
                    $format
                );
                mysqli_stmt_execute($ingredient_stmt);
            }
        }

        // Commit transaction
        mysqli_commit($db);

        echo json_encode([
            "message" => "Recipe updated successfully",
            "recipe_id" => $data['id']
        ], JSON_PRETTY_PRINT);
        die();

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($db);
        http_response_code(500);
        echo json_encode([
            "message" => "Failed to update recipe",
            "error" => $e->getMessage()
        ], JSON_PRETTY_PRINT);
        die();
    }
  }

  // Delete a recipe
  if ($_SERVER["REQUEST_METHOD"] == "DELETE") {
    // Get recipe ID from query parameters
    $id = $_GET["id"] ?? null;

    // Validate recipe ID
    if ($id === null) {
        http_response_code(400);
        echo json_encode(["message" => "Recipe ID is required"], JSON_PRETTY_PRINT);
        die();
    }

    // Start transaction
    mysqli_begin_transaction($db);
    try {
        // Check if recipe exists
        $check_stmt = mysqli_prepare($db, "SELECT id FROM recipes WHERE id = ?");
        mysqli_stmt_bind_param($check_stmt, "i", $id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($result) === 0) {
            http_response_code(404);
            echo json_encode(["message" => "Recipe not found"], JSON_PRETTY_PRINT);
            die();
        }

        // Delete recipe (associated ingredients will be deleted automatically due to CASCADE)
        $delete_stmt = mysqli_prepare($db, "DELETE FROM recipes WHERE id = ?");
        mysqli_stmt_bind_param($delete_stmt, "i", $id);
        
        if (!mysqli_stmt_execute($delete_stmt)) {
            throw new Exception("Failed to delete recipe");
        }

        // Commit transaction
        mysqli_commit($db);

        echo json_encode([
            "message" => "Recipe deleted successfully",
            "recipe_id" => $id
        ], JSON_PRETTY_PRINT);
        die();

    } catch (Exception $e) {
        // Rollback transaction on error
        mysqli_rollback($db);
        http_response_code(500);
        echo json_encode([
            "message" => "Failed to delete recipe",
            "error" => $e->getMessage()
        ], JSON_PRETTY_PRINT);
        die();
    }
  }

  http_response_code(405);
  echo json_encode(["message" => "Invalid request method"], JSON_PRETTY_PRINT);


  require "../end.php";