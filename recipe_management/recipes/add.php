<?php
// recipes/add.php - Add a new recipe
require_once '../includes/header.php';

// Check if user is logged in
check_login();

$error = '';
$success = false;
$title = '';
$description = '';
$ingredients = '';
$instructions = '';
$cooking_time = '';
$servings = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $ingredients = sanitize_input($_POST['ingredients']);
    $instructions = sanitize_input($_POST['instructions']);
    $cooking_time = sanitize_input($_POST['cooking_time']);
    $servings = sanitize_input($_POST['servings']);
    $user_id = $_SESSION['user_id'];
    
    if (empty($title) || empty($ingredients) || empty($instructions)) {
        $error = "Title, ingredients, and instructions are required";
    } else {
        $image = '';
        
        // Handle image upload if provided
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['image']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $file_name = time() . '_' . $_FILES['image']['name'];
                $upload_dir = '../uploads/';
                
                // Create upload directory if it doesn't exist
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image = $file_name;
                } else {
                    $error = "Error uploading image";
                }
            } else {
                $error = "Invalid image format. Only JPEG, PNG, and GIF are allowed";
            }
        }
        
        if (empty($error)) {
            // Insert recipe into database
            $stmt = $conn->prepare("INSERT INTO recipes (user_id, title, description, ingredients, instructions, cooking_time, servings, image, created_at) 
                                   VALUES (:user_id, :title, :description, :ingredients, :instructions, :cooking_time, :servings, :image, NOW())");
            $stmt->bindParam(':user_id', $user_id);
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':ingredients', $ingredients);
            $stmt->bindParam(':instructions', $instructions);
            $stmt->bindParam(':cooking_time', $cooking_time);
            $stmt->bindParam(':servings', $servings);
            $stmt->bindParam(':image', $image);
            
            if ($stmt->execute()) {
                $success = true;
                // Clear form fields
                $title = $description = $ingredients = $instructions = $cooking_time = $servings = '';
            } else {
                $error = "Error adding recipe. Please try again.";
            }
        }
    }
}
?>

<div class="page-header">
    <h2>Add New Recipe</h2>
</div>

<div class="form-container recipe-form">
    <?php if ($success): ?>
        <div class="alert alert-success">Recipe added successfully!</div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form action="add.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Recipe Title</label>
            <input type="text" id="title" name="title" class="form-control" value="<?php echo $title; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control"><?php echo $description; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="ingredients">Ingredients</label>
            <textarea id="ingredients" name="ingredients" class="form-control" required><?php echo $ingredients; ?></textarea>
            <small>Enter one ingredient per line</small>
        </div>
        
        <div class="form-group">
            <label for="instructions">Instructions</label>
            <textarea id="instructions" name="instructions" class="form-control" required><?php echo $instructions; ?></textarea>
            <small>Enter detailed cooking instructions</small>
        </div>
        
        <div class="form-row">
            <div class="form-group half">
                <label for="cooking_time">Cooking Time (minutes)</label>
                <input type="number" id="cooking_time" name="cooking_time" class="form-control" value="<?php echo $cooking_time; ?>">
            </div>
            
            <div class="form-group half">
                <label for="servings">Servings</label>
                <input type="number" id="servings" name="servings" class="form-control" value="<?php echo $servings; ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="image">Recipe Image</label>
            <input type="file" id="image" name="image" class="form-control">
            <small>Accepted formats: JPEG, PNG, GIF</small>
            <div id="image-preview" class="image-preview"></div>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Add Recipe</button>
            <a href="index.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?> 