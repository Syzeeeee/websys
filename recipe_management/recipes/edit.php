<?php
// recipes/edit.php - Edit an existing recipe
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
    echo "<div class='alert alert-danger'>You are not authorized to edit this recipe</div>";
    require_once '../includes/footer.php';
    exit;
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitize_input($_POST['title']);
    $description = sanitize_input($_POST['description']);
    $ingredients = sanitize_input($_POST['ingredients']);
    $instructions = sanitize_input($_POST['instructions']);
    $cooking_time = sanitize_input($_POST['cooking_time']);
    $servings = sanitize_input($_POST['servings']);
    
    if (empty($title) || empty($ingredients) || empty($instructions)) {
        $error = "Title, ingredients, and instructions are required";
    } else {
        $image = $recipe['image']; // Keep existing image by default
        
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
                    // Delete old image if exists
                    if (!empty($recipe['image']) && file_exists($upload_dir . $recipe['image'])) {
                        unlink($upload_dir . $recipe['image']);
                    }
                    $image = $file_name;
                } else {
                    $error = "Error uploading image";
                }
            } else {
                $error = "Invalid image format. Only JPEG, PNG, and GIF are allowed";
            }
        }
        
        if (empty($error)) {
            // Update recipe in database
            $stmt = $conn->prepare("UPDATE recipes SET title = :title, description = :description, ingredients = :ingredients, 
                                   instructions = :instructions, cooking_time = :cooking_time, servings = :servings, image = :image 
                                   WHERE id = :id AND user_id = :user_id");
            $stmt->bindParam(':title', $title);
            $stmt->bindParam(':description', $description);
            $stmt->bindParam(':ingredients', $ingredients);
            $stmt->bindParam(':instructions', $instructions);
            $stmt->bindParam(':cooking_time', $cooking_time);
            $stmt->bindParam(':servings', $servings);
            $stmt->bindParam(':image', $image);
            $stmt->bindParam(':id', $recipe_id);
            $stmt->bindParam(':user_id', $_SESSION['user_id']);
            
            if ($stmt->execute()) {
                $success = true;
                // Update recipe data for the form
                $recipe['title'] = $title;
                $recipe['description'] = $description;
                $recipe['ingredients'] = $ingredients;
                $recipe['instructions'] = $instructions;
                $recipe['cooking_time'] = $cooking_time;
                $recipe['servings'] = $servings;
                $recipe['image'] = $image;
            } else {
                $error = "Error updating recipe. Please try again.";
            }
        }
    }
}
?>

<div class="page-header">
    <h2>Edit Recipe</h2>
</div>

<div class="form-container recipe-form">
    <?php if ($success): ?>
        <div class="alert alert-success">Recipe updated successfully!</div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <form action="edit.php?id=<?php echo $recipe_id; ?>" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label for="title">Recipe Title</label>
            <input type="text" id="title" name="title" class="form-control" value="<?php echo $recipe['title']; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" class="form-control"><?php echo $recipe['description']; ?></textarea>
        </div>
        
        <div class="form-group">
            <label for="ingredients">Ingredients</label>
            <textarea id="ingredients" name="ingredients" class="form-control" required><?php echo $recipe['ingredients']; ?></textarea>
            <small>Enter one ingredient per line</small>
        </div>
        
        <div class="form-group">
            <label for="instructions">Instructions</label>
            <textarea id="instructions" name="instructions" class="form-control" required><?php echo $recipe['instructions']; ?></textarea>
            <small>Enter detailed cooking instructions</small>
        </div>
        
        <div class="form-row">
            <div class="form-group half">
                <label for="cooking_time">Cooking Time (minutes)</label>
                <input type="number" id="cooking_time" name="cooking_time" class="form-control" value="<?php echo $recipe['cooking_time']; ?>">
            </div>
            
            <div class="form-group half">
                <label for="servings">Servings</label>
                <input type="number" id="servings" name="servings" class="form-control" value="<?php echo $recipe['servings']; ?>">
            </div>
        </div>
        
        <div class="form-group">
            <label for="image">Recipe Image</label>
            <?php if (!empty($recipe['image'])): ?>
                <div class="current-image">
                    <img src="../uploads/<?php echo $recipe['image']; ?>" alt="Current Image" style="max-width: 200px;">
                    <p>Current Image</p>
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" class="form-control">
            <small>Leave empty to keep current image. Accepted formats: JPEG, PNG, GIF</small>
        </div>
        
        <div class="form-group">
            <button type="submit" class="btn">Update Recipe</button>
            <a href="view.php?id=<?php echo $recipe_id; ?>" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php require_once '../includes/footer.php'; ?> 