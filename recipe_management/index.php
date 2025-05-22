<?php
// index.php - Main page of the Recipe Management System
require_once 'includes/header.php';
?>
<div class="hero">
    <h2>Welcome to Recipe Management System</h2>
    <p>Discover, create, and share your favorite recipes</p>
    <div class="cta-buttons">
        <a href="recipes/index.php" class="btn">Browse Recipes</a>
        <?php if(isset($_SESSION['user_id'])): ?>
            <a href="recipes/add.php" class="btn">Add New Recipe</a>
        <?php else: ?>
            <a href="auth/register.php" class="btn">Join Now</a>
        <?php endif; ?>
    </div>
</div>

<div class="features">
    <div class="feature">
        <i class="fas fa-utensils"></i>
        <h3>Discover Recipes</h3>
        <p>Browse through a collection of delicious recipes from around the world.</p>
    </div>
    <div class="feature">
        <i class="fas fa-edit"></i>
        <h3>Create & Share</h3>
        <p>Create your own recipes and share them with the community.</p>
    </div>
    <div class="feature">
        <i class="fas fa-user"></i>
        <h3>User Profiles</h3>
        <p>Keep track of your favorite recipes and contributions.</p>
    </div>
</div>
<?php require_once 'includes/footer.php'; ?>
