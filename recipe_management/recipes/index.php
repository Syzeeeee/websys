<?php
// recipes/index.php - List all recipes
require_once '../includes/header.php';

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$recipes = [];

if (!empty($search)) {
    $recipes = search_recipes($search);
} else {
    $recipes = get_all_recipes();
}
?>

<div class="page-header">
    <h2>Recipes</h2>
    <form id="search-form" class="search-form">
        <input type="text" id="search-input" name="search" placeholder="Search recipes..." value="<?php echo $search; ?>">
        <button type="submit" class="btn"><i class="fas fa-search"></i></button>
    </form>
</div>

<?php if (!empty($search)): ?>
    <div class="search-results-header">
        <h3>Search Results for "<?php echo $search; ?>"</h3>
        <a href="index.php" class="btn">Clear Search</a>
    </div>
<?php endif; ?>

<?php if (empty($recipes)): ?>
    <div class="no-results">
        <p>No recipes found. <?php echo !empty($search) ? 'Try a different search term.' : ''; ?></p>
        <?php if (is_logged_in()): ?>
            <a href="add.php" class="btn">Add a Recipe</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <div class="recipes-grid">
        <?php foreach ($recipes as $recipe): ?>
            <div class="recipe-card">
                <div class="recipe-image" style="background-image: url('../uploads/<?php echo !empty($recipe['image']) ? $recipe['image'] : 'default-recipe.jpg'; ?>')"></div>
                <div class="recipe-info">
                    <h3><a href="view.php?id=<?php echo $recipe['id']; ?>"><?php echo $recipe['title']; ?></a></h3>
                    <div class="recipe-meta">
                        <span><i class="fas fa-user"></i> <?php echo $recipe['username']; ?></span>
                        <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($recipe['created_at'])); ?></span>
                    </div>
                    <p><?php echo substr($recipe['description'], 0, 100) . (strlen($recipe['description']) > 100 ? '...' : ''); ?></p>
                    <a href="view.php?id=<?php echo $recipe['id']; ?>" class="btn">View Recipe</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?> 