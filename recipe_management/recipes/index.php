<?php
// recipes/index.php - List all recipes
require_once '../includes/header.php';

$search = isset($_GET['search']) ? sanitize_input($_GET['search']) : '';
$category = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$sort = isset($_GET['sort']) ? sanitize_input($_GET['sort']) : 'date_desc';
$recipes = [];

if (!empty($search)) {
    $recipes = search_recipes($search, $category, $sort);
} else {
    $recipes = get_all_recipes(null, $category, $sort);
}

// Get current sort information
$sort_options = [
    'title_asc' => 'Name (A-Z)',
    'title_desc' => 'Name (Z-A)',
    'date_desc' => 'Newest First',
    'date_asc' => 'Oldest First',
];
?>

<div class="page-header">
    <h2>Recipes</h2>
    <form id="search-form" class="search-form" method="GET">
        <div class="search-controls">
            <div class="search-row">
                <input type="text" id="search-input" name="search" placeholder="Search recipes..." value="<?php echo $search; ?>">
                <select name="category" id="category-filter">
                    <option value="">All Categories</option>
                    <option value="appetizer" <?php echo $category === 'appetizer' ? 'selected' : ''; ?>>Appetizers</option>
                    <option value="main course" <?php echo $category === 'main course' ? 'selected' : ''; ?>>Main Course</option>
                    <option value="dessert" <?php echo $category === 'dessert' ? 'selected' : ''; ?>>Desserts</option>
                </select>
            </div>
            <div class="sort-row">
                <select name="sort" id="sort-filter">
                    <?php foreach ($sort_options as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo $sort === $value ? 'selected' : ''; ?>>
                            Sort by: <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn"><i class="fas fa-search"></i> Search</button>
            </div>
        </div>
    </form>
</div>

<?php if (!empty($search) || !empty($category) || $sort !== 'date_desc'): ?>
    <div class="search-results-header">
        <h3>
            <?php if (!empty($search)): ?>
                Search Results for "<?php echo $search; ?>"
            <?php endif; ?>
            <?php if (!empty($category)): ?>
                <?php echo ucwords($category); ?>
            <?php endif; ?>
            <?php if ($sort !== 'date_desc'): ?>
                - Sorted by <?php echo $sort_options[$sort]; ?>
            <?php endif; ?>
        </h3>
        <a href="index.php" class="btn">Clear Filters</a>
    </div>
<?php endif; ?>

<?php if (empty($recipes)): ?>
    <div class="no-results">
        <p>No recipes found. <?php echo !empty($search) ? 'Try a different search term or category.' : ''; ?></p>
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
                        <span><i class="fas fa-utensils"></i> <?php echo ucwords($recipe['category']); ?></span>
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