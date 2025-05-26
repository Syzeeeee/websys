<?php
require_once 'includes/header.php';

// Check if user is logged in
check_login();

$user_id = $_SESSION['user_id'];
$category_filter = isset($_GET['category']) ? sanitize_input($_GET['category']) : '';
$sort = isset($_GET['sort']) ? sanitize_input($_GET['sort']) : 'date_desc';

// Get user information
$stmt = $conn->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get user's created recipes
$stmt = $conn->prepare("
    SELECT * FROM recipes 
    WHERE user_id = ? 
    ORDER BY created_at DESC
");
$stmt->execute([$user_id]);
$created_recipes = $stmt->fetchAll();

// Get user's favorite recipes with filtering and sorting
$favorite_query = "
    SELECT r.*, u.username, f.created_at as favorited_at
    FROM recipes r 
    JOIN favorites f ON r.id = f.recipe_id 
    JOIN users u ON r.user_id = u.id 
    WHERE f.user_id = ?
";

$params = [$user_id];

if (!empty($category_filter)) {
    $favorite_query .= " AND r.category = ?";
    $params[] = $category_filter;
}

// Add sorting
switch ($sort) {
    case 'title_asc':
        $favorite_query .= " ORDER BY r.title ASC";
        break;
    case 'title_desc':
        $favorite_query .= " ORDER BY r.title DESC";
        break;
    case 'date_asc':
        $favorite_query .= " ORDER BY f.created_at ASC";
        break;
    default: // date_desc
        $favorite_query .= " ORDER BY f.created_at DESC";
}

$stmt = $conn->prepare($favorite_query);
$stmt->execute($params);
$favorite_recipes = $stmt->fetchAll();

// Define sort options
$sort_options = [
    'date_desc' => 'Recently Favorited',
    'date_asc' => 'Oldest Favorites',
    'title_asc' => 'Title (A-Z)',
    'title_desc' => 'Title (Z-A)'
];

?>

<div class="profile-container">
    <div class="profile-header">
        <h2>My Profile</h2>
        <div class="profile-info">
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Member since:</strong> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
        </div>
    </div>

    <div class="profile-content">
        <!-- Tabs -->
        <div class="profile-tabs">
            <button class="tab-btn <?php echo empty($_GET['tab']) || $_GET['tab'] === 'my-recipes' ? 'active' : ''; ?>" 
                    data-tab="my-recipes">My Recipes (<?php echo count($created_recipes); ?>)</button>
            <button class="tab-btn <?php echo isset($_GET['tab']) && $_GET['tab'] === 'favorite-recipes' ? 'active' : ''; ?>" 
                    data-tab="favorite-recipes">Favorite Recipes (<?php echo count($favorite_recipes); ?>)</button>
        </div>

        <!-- My Recipes Tab -->
        <div class="tab-content <?php echo empty($_GET['tab']) || $_GET['tab'] === 'my-recipes' ? 'active' : ''; ?>" id="my-recipes">
            <?php if (empty($created_recipes)): ?>
                <div class="no-results">
                    <p>You haven't created any recipes yet.</p>
                    <a href="recipes/add.php" class="btn">Create Your First Recipe</a>
                </div>
            <?php else: ?>
                <div class="recipes-grid">
                    <?php foreach ($created_recipes as $recipe): ?>
                        <div class="recipe-card">
                            <div class="recipe-image" style="background-image: url('uploads/<?php echo !empty($recipe['image']) ? $recipe['image'] : 'default-recipe.jpg'; ?>')"></div>
                            <div class="recipe-info">
                                <h3><a href="recipes/view.php?id=<?php echo $recipe['id']; ?>"><?php echo htmlspecialchars($recipe['title']); ?></a></h3>
                                <div class="recipe-meta">
                                    <span><i class="fas fa-utensils"></i> <?php echo ucwords($recipe['category']); ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($recipe['created_at'])); ?></span>
                                </div>
                                <p><?php echo substr($recipe['description'], 0, 100) . (strlen($recipe['description']) > 100 ? '...' : ''); ?></p>
                                <div class="recipe-actions">
                                    <a href="recipes/view.php?id=<?php echo $recipe['id']; ?>" class="btn">View Recipe</a>
                                    <a href="recipes/edit.php?id=<?php echo $recipe['id']; ?>" class="btn btn-secondary">Edit</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Favorite Recipes Tab -->
        <div class="tab-content <?php echo isset($_GET['tab']) && $_GET['tab'] === 'favorite-recipes' ? 'active' : ''; ?>" id="favorite-recipes">
            <?php if (empty($favorite_recipes)): ?>
                <div class="no-results">
                    <p>You haven't favorited any recipes yet.</p>
                    <a href="recipes/index.php" class="btn">Browse Recipes</a>
                </div>
            <?php else: ?>
                <!-- Filter and Sort Controls -->
                <div class="filter-sort-controls">
                    <form id="filter-form" class="filter-form" method="GET">
                        <input type="hidden" name="tab" value="favorite-recipes">
                        <div class="control-group">
                            <select name="category" id="category-filter" class="form-control">
                                <option value="">All Categories</option>
                                <option value="appetizer" <?php echo $category_filter === 'appetizer' ? 'selected' : ''; ?>>Appetizers</option>
                                <option value="main course" <?php echo $category_filter === 'main course' ? 'selected' : ''; ?>>Main Course</option>
                                <option value="dessert" <?php echo $category_filter === 'dessert' ? 'selected' : ''; ?>>Desserts</option>
                            </select>
                            <select name="sort" id="sort-filter" class="form-control">
                                <?php foreach ($sort_options as $value => $label): ?>
                                    <option value="<?php echo $value; ?>" <?php echo $sort === $value ? 'selected' : ''; ?>>
                                        Sort by: <?php echo $label; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn">Apply</button>
                            <?php if (!empty($category_filter) || $sort !== 'date_desc'): ?>
                                <a href="?tab=favorite-recipes" class="btn btn-secondary">Clear Filters</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <div class="recipes-grid">
                    <?php foreach ($favorite_recipes as $recipe): ?>
                        <div class="recipe-card">
                            <button class="favorite-btn active" data-recipe-id="<?php echo $recipe['id']; ?>">
                                <i class="fas fa-heart"></i>
                            </button>
                            <div class="recipe-image" style="background-image: url('uploads/<?php echo !empty($recipe['image']) ? $recipe['image'] : 'default-recipe.jpg'; ?>')"></div>
                            <div class="recipe-info">
                                <h3><a href="recipes/view.php?id=<?php echo $recipe['id']; ?>"><?php echo htmlspecialchars($recipe['title']); ?></a></h3>
                                <div class="recipe-meta">
                                    <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($recipe['username']); ?></span>
                                    <span><i class="fas fa-utensils"></i> <?php echo ucwords($recipe['category']); ?></span>
                                    <span><i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($recipe['created_at'])); ?></span>
                                </div>
                                <p><?php echo substr($recipe['description'], 0, 100) . (strlen($recipe['description']) > 100 ? '...' : ''); ?></p>
                                <a href="recipes/view.php?id=<?php echo $recipe['id']; ?>" class="btn">View Recipe</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.profile-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.profile-header {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.profile-info {
    margin-top: 15px;
}

.profile-info p {
    margin: 8px 0;
    color: #666;
}

.profile-tabs {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}

.tab-btn {
    padding: 10px 20px;
    border: none;
    background: #f0f0f0;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    transition: all 0.3s ease;
}

.tab-btn.active {
    background: #007bff;
    color: white;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
}

.recipe-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.btn-secondary {
    background: #6c757d;
}

.btn-secondary:hover {
    background: #5a6268;
}

.filter-sort-controls {
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.filter-form {
    display: flex;
    gap: 15px;
}

.control-group {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.form-control {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    min-width: 150px;
}

.form-control:focus {
    border-color: #007bff;
    outline: none;
}

@media (max-width: 768px) {
    .control-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .form-control {
        width: 100%;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tab switching with URL parameter update
    const tabBtns = document.querySelectorAll('.tab-btn');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabName = btn.dataset.tab;
            const currentUrl = new URL(window.location.href);
            
            // Update URL with new tab
            currentUrl.searchParams.set('tab', tabName);
            window.history.pushState({}, '', currentUrl);
            
            // Update UI
            tabBtns.forEach(b => b.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            
            btn.classList.add('active');
            document.getElementById(tabName).classList.add('active');
        });
    });

    // Auto-submit form when filters change
    const filterForm = document.getElementById('filter-form');
    const filterInputs = filterForm.querySelectorAll('select');
    
    filterInputs.forEach(input => {
        input.addEventListener('change', () => {
            filterForm.submit();
        });
    });

    // Favorite button functionality
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', async function(e) {
            e.preventDefault();
            const recipeId = this.dataset.recipeId;
            
            try {
                const response = await fetch('recipes/toggle_favorite.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `recipe_id=${recipeId}`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // If unfavorited, remove the recipe card and update counts
                    if (!data.is_favorited) {
                        const recipeCard = this.closest('.recipe-card');
                        recipeCard.remove();
                        
                        // Update favorite count in tab button
                        const favoritesTab = document.querySelector('[data-tab="favorite-recipes"]');
                        const currentCount = parseInt(favoritesTab.textContent.match(/\((\d+)\)/)[1]);
                        const newCount = currentCount - 1;
                        favoritesTab.textContent = favoritesTab.textContent.replace(
                            /\(\d+\)/, 
                            `(${newCount})`
                        );

                        // Show no results message if no favorites left
                        const recipesGrid = document.querySelector('#favorite-recipes .recipes-grid');
                        if (recipesGrid && recipesGrid.children.length === 0) {
                            const noResults = document.createElement('div');
                            noResults.className = 'no-results';
                            noResults.innerHTML = `
                                <p>You haven't favorited any recipes yet.</p>
                                <a href="recipes/index.php" class="btn">Browse Recipes</a>
                            `;
                            document.querySelector('#favorite-recipes').appendChild(noResults);
                        }
                    }
                } else {
                    alert('Error: ' + data.message);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred while updating favorite status');
            }
        });
    });
});
</script>

<?php require_once 'includes/footer.php'; ?> 