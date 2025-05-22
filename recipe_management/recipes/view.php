<?php
// recipes/view.php - View recipe details
require_once '../includes/header.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    redirect('index.php');
}

$recipe_id = $_GET['id'];
$recipe = get_recipe_by_id($recipe_id);

if (!$recipe) {
    echo "<div class='alert alert-danger'>Recipe not found</div>";
    require_once '../includes/footer.php';
    exit;
}

// Check if the current user owns this recipe
$is_owner = is_logged_in() && $_SESSION['user_id'] == $recipe['user_id'];
?>

<div class="recipe-detail">
    <div class="recipe-actions">
        <a href="index.php" class="btn"><i class="fas fa-arrow-left"></i> Back to Recipes</a>
        
        <?php if ($is_owner): ?>
            <div class="owner-actions">
                <a href="edit.php?id=<?php echo $recipe['id']; ?>" class="btn"><i class="fas fa-edit"></i> Edit</a>
                <a href="delete.php?id=<?php echo $recipe['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this recipe?')"><i class="fas fa-trash"></i> Delete</a>
            </div>
        <?php endif; ?>
    </div>
    
    <div class="recipe-header">
        <h2><?php echo $recipe['title']; ?></h2>
        
        <div class="recipe-meta">
            <span><i class="fas fa-user"></i> By <?php echo $recipe['username']; ?></span>
            <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($recipe['created_at'])); ?></span>
            
            <?php if (!empty($recipe['cooking_time'])): ?>
                <span><i class="fas fa-clock"></i> <?php echo $recipe['cooking_time']; ?> minutes</span>
            <?php endif; ?>
            
            <?php if (!empty($recipe['servings'])): ?>
                <span><i class="fas fa-utensils"></i> <?php echo $recipe['servings']; ?> servings</span>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if (!empty($recipe['image'])): ?>
        <div class="recipe-image-full">
            <img src="../uploads/<?php echo $recipe['image']; ?>" alt="<?php echo $recipe['title']; ?>">
        </div>
    <?php endif; ?>
    
    <?php if (!empty($recipe['description'])): ?>
        <div class="recipe-section">
            <h3>Description</h3>
            <p><?php echo nl2br($recipe['description']); ?></p>
        </div>
    <?php endif; ?>
    
    <div class="recipe-section">
        <h3>Ingredients</h3>
        <ul class="ingredients-list">
            <?php
            $ingredients_list = explode("\n", $recipe['ingredients']);
            foreach ($ingredients_list as $ingredient) {
                if (!empty(trim($ingredient))) {
                    echo "<li>" . trim($ingredient) . "</li>";
                }
            }
            ?>
        </ul>
    </div>
    
    <div class="recipe-section">
        <h3>Instructions</h3>
        <div class="instructions">
            <?php echo nl2br($recipe['instructions']); ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 