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

function get_all_recipes($limit = null) {
    global $conn;
    $query = "SELECT r.*, u.username FROM recipes r 
              JOIN users u ON r.user_id = u.id 
              ORDER BY r.created_at DESC";
    
    if ($limit) {
        $query .= " LIMIT " . intval($limit);
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function search_recipes($keyword) {
    global $conn;
    $search = "%$keyword%";
    $stmt = $conn->prepare("SELECT r.*, u.username FROM recipes r 
                           JOIN users u ON r.user_id = u.id 
                           WHERE r.title LIKE :keyword 
                           OR r.ingredients LIKE :keyword 
                           OR r.instructions LIKE :keyword");
    $stmt->bindParam(':keyword', $search);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
