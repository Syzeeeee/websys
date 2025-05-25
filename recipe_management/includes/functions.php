<?php
// includes/functions.php - Common utility functions

function redirect($location) {
    header("Location: $location");
    exit;
}

function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function check_login() {
    if (!is_logged_in()) {
        redirect('../auth/login.php');
    }
}

function display_message($message, $type = 'success') {
    if (!empty($message)) {
        echo "<div class='alert alert-{$type}'>{$message}</div>";
    }
}

function get_user_by_id($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_recipe_by_id($recipe_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT r.*, u.username FROM recipes r 
                           JOIN users u ON r.user_id = u.id 
                           WHERE r.id = :id");
    $stmt->bindParam(':id', $recipe_id);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function get_all_recipes($limit = null, $category = null, $sort = 'date_desc') {
    global $conn;
    $query = "SELECT r.*, u.username FROM recipes r 
              JOIN users u ON r.user_id = u.id";
    
    if ($category) {
        $query .= " WHERE r.category = :category";
    }
    
    // Add sorting
    switch ($sort) {
        case 'title_asc':
            $query .= " ORDER BY r.title ASC";
            break;
        case 'title_desc':
            $query .= " ORDER BY r.title DESC";
            break;
        case 'date_asc':
            $query .= " ORDER BY r.created_at ASC";
            break;
        case 'category_asc':
            $query .= " ORDER BY r.category ASC, r.title ASC";
            break;
        case 'category_desc':
            $query .= " ORDER BY r.category DESC, r.title ASC";
            break;
        case 'date_desc':
        default:
            $query .= " ORDER BY r.created_at DESC";
    }
    
    if ($limit) {
        $query .= " LIMIT " . intval($limit);
    }
    
    $stmt = $conn->prepare($query);
    
    if ($category) {
        $stmt->bindParam(':category', $category);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function search_recipes($keyword, $category = null, $sort = 'date_desc') {
    global $conn;
    $search = "%$keyword%";
    $query = "SELECT r.*, u.username FROM recipes r 
              JOIN users u ON r.user_id = u.id 
              WHERE (r.title LIKE :keyword 
              OR r.ingredients LIKE :keyword 
              OR r.instructions LIKE :keyword)";
    
    if ($category) {
        $query .= " AND r.category = :category";
    }
    
    // Add sorting
    switch ($sort) {
        case 'title_asc':
            $query .= " ORDER BY r.title ASC";
            break;
        case 'title_desc':
            $query .= " ORDER BY r.title DESC";
            break;
        case 'date_asc':
            $query .= " ORDER BY r.created_at ASC";
            break;
        case 'category_asc':
            $query .= " ORDER BY r.category ASC, r.title ASC";
            break;
        case 'category_desc':
            $query .= " ORDER BY r.category DESC, r.title ASC";
            break;
        case 'date_desc':
        default:
            $query .= " ORDER BY r.created_at DESC";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':keyword', $search);
    
    if ($category) {
        $stmt->bindParam(':category', $category);
    }
    
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function get_user_recipes($user_id, $sort = 'date_desc') {
    global $conn;
    $query = "SELECT r.*, u.username FROM recipes r 
              JOIN users u ON r.user_id = u.id 
              WHERE r.user_id = :user_id";
    
    // Add sorting
    switch ($sort) {
        case 'title_asc':
            $query .= " ORDER BY r.title ASC";
            break;
        case 'title_desc':
            $query .= " ORDER BY r.title DESC";
            break;
        case 'date_asc':
            $query .= " ORDER BY r.created_at ASC";
            break;
        case 'category_asc':
            $query .= " ORDER BY r.category ASC, r.title ASC";
            break;
        case 'category_desc':
            $query .= " ORDER BY r.category DESC, r.title ASC";
            break;
        case 'date_desc':
        default:
            $query .= " ORDER BY r.created_at DESC";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function update_user_profile($user_id, $data) {
    global $conn;
    $fields = [];
    $params = [':user_id' => $user_id];
    
    // Build update query based on provided data
    if (!empty($data['email'])) {
        $fields[] = "email = :email";
        $params[':email'] = $data['email'];
    }
    
    if (!empty($data['password'])) {
        $fields[] = "password = :password";
        $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }
    
    if (empty($fields)) {
        return false;
    }
    
    $query = "UPDATE users SET " . implode(", ", $fields) . " WHERE id = :user_id";
    $stmt = $conn->prepare($query);
    
    try {
        return $stmt->execute($params);
    } catch (PDOException $e) {
        // Handle unique constraint violations
        if ($e->getCode() == 23000) {
            return "Email address already in use";
        }
        return false;
    }
}

function count_user_recipes($user_id) {
    global $conn;
    $stmt = $conn->prepare("SELECT COUNT(*) FROM recipes WHERE user_id = :user_id");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    return $stmt->fetchColumn();
}

function get_user_stats($user_id) {
    global $conn;
    $stats = [
        'total_recipes' => 0,
        'recipes_by_category' => [],
        'latest_recipe' => null
    ];
    
    // Get total recipes
    $stats['total_recipes'] = count_user_recipes($user_id);
    
    // Get recipes by category
    $stmt = $conn->prepare("SELECT category, COUNT(*) as count 
                           FROM recipes 
                           WHERE user_id = :user_id 
                           GROUP BY category");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $stats['recipes_by_category'] = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    
    // Get latest recipe
    $stmt = $conn->prepare("SELECT * FROM recipes 
                           WHERE user_id = :user_id 
                           ORDER BY created_at DESC 
                           LIMIT 1");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->execute();
    $stats['latest_recipe'] = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $stats;
}
