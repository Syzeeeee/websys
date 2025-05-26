<?php
require_once '../includes/header.php';

// Check if user is logged in
check_login();

// Ensure it's a POST request with recipe_id
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['recipe_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$recipe_id = $_POST['recipe_id'];
$user_id = $_SESSION['user_id'];

try {
    // Check if recipe exists
    $check_recipe = $conn->prepare("SELECT id FROM recipes WHERE id = ?");
    $check_recipe->execute([$recipe_id]);
    if (!$check_recipe->fetch()) {
        throw new Exception('Recipe not found');
    }

    // Check if already favorited
    $check_favorite = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND recipe_id = ?");
    $check_favorite->execute([$user_id, $recipe_id]);
    $existing_favorite = $check_favorite->fetch();

    if ($existing_favorite) {
        // Remove favorite
        $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
        $stmt->execute([$user_id, $recipe_id]);
        $is_favorited = false;
    } else {
        // Add favorite
        $stmt = $conn->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)");
        $stmt->execute([$user_id, $recipe_id]);
        $is_favorited = true;
    }

    echo json_encode([
        'success' => true,
        'is_favorited' => $is_favorited
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 