<?php
// auth/profile.php
require_once '../includes/header.php';

// Check if user is logged in
check_login();

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($user_id);
$user_recipes = get_user_recipes($user_id);
?>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-info">
            <i class="fas fa-user-circle profile-icon"></i>
            <h2><?php echo htmlspecialchars($user['username']); ?>'s Profile</h2>
            <p class="profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
            <p class="profile-stats">
                <span><i class="fas fa-utensils"></i> <?php echo count($user_recipes); ?> Recipes</span>
                <span><i class="fas fa-calendar-alt"></i> Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></span>
            </p>
        </div>
    </div>

    <div class="profile-content">
        <div class="profile-section">
            <h3>My Recipes</h3>
            <?php if (empty($user_recipes)): ?>
                <div class="no-recipes">
                    <p>You haven't added any recipes yet.</p>
                    <a href="../recipes/add.php" class="btn">Add Your First Recipe</a>
                </div>
            <?php else: ?>
                <div class="recipes-grid">
                    <?php foreach ($user_recipes as $recipe): ?>
                        <div class="recipe-card">
                            <div class="recipe-image" style="background-image: url('../uploads/<?php echo !empty($recipe['image']) ? $recipe['image'] : 'default-recipe.jpg'; ?>')"></div>
                            <div class="recipe-info">
                                <h3><a href="../recipes/view.php?id=<?php echo $recipe['id']; ?>"><?php echo htmlspecialchars($recipe['title']); ?></a></h3>
                                <div class="recipe-meta">
                                    <span><i class="fas fa-utensils"></i> <?php echo ucwords($recipe['category']); ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($recipe['created_at'])); ?></span>
                                </div>
                                <div class="recipe-actions">
                                    <a href="../recipes/edit.php?id=<?php echo $recipe['id']; ?>" class="btn btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="../recipes/delete.php?id=<?php echo $recipe['id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this recipe?');"><i class="fas fa-trash"></i> Delete</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 