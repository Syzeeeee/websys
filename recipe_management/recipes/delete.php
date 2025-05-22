<?php
// recipes/delete.php - Delete a recipe
require_once '../includes/header.php';

// Check if user is logged in
check_login();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php');
}

$recipe_id = $_GET['id'];
$recipe = get_recipe_by_id($recipe_id);

// Check if recipe exists and user owns it
if (!$recipe || $recipe['user_id'] != $_SESSION['user_id']) {
    echo "<div class='alert alert-danger'>You are not authorized to delete this recipe</div>";
    require_once '../includes/footer.php';
    exit;
}

$error = '';
$success = false;

// If confirmed
if (isset($_POST['confirm']) && $_POST['confirm'] === 'yes') {
    // Delete recipe image if it exists
    if (!empty($recipe['image'])) {
        $image_path = '../uploads/' . $recipe['image'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Delete recipe from database
    $stmt = $conn->prepare("DELETE FROM recipes WHERE id = :id AND user_id = :user_id");
    $stmt->bindParam(':id', $recipe_id);
    $stmt->bindParam(':user_id', $_SESSION['user_id']);
    
    if ($stmt->execute()) {
        // Redirect to recipes page
        redirect('index.php');
    } else {
        $error = "Error deleting recipe. Please try again.";
    }
}
?>

<div class="page-header">
    <h2>Delete Recipe</h2>
</div>

<div class="form-container">
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <div class="alert alert-warning">
        <p>Are you sure you want to delete the recipe "<strong><?php echo $recipe['title']; ?></strong>"?</p>
        <p>This action cannot be undone.</p>
    </div>
    
    <form action="delete.php?id=<?php echo $recipe_id; ?>" method="POST">
        <div class="form-group">
            <input type="hidden" name="confirm" value="yes">
            <button type="submit" class="btn btn-danger">Yes, Delete Recipe</button>
            <a href="view.php?id=<?php echo $recipe_id; ?>" class="btn">No, Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?> 