<?php
// includes/header.php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recipe Management System</title>
    <link rel="stylesheet" href="/recipe_management/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
</head>
<body>
    <header>
        <div class="container">
            <div class="logo">
                <h1><a href="/recipe_management/index.php">Recipe Management</a></h1>
            </div>
            <nav>
                <ul>
                    <li><a href="/recipe_management/index.php">Home</a></li>
                    <li><a href="/recipe_management/recipes/index.php">Recipes</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <li><a href="/recipe_management/recipes/add.php">Add Recipe</a></li>
                        <li><a href="/recipe_management/profile.php"><i class="fas fa-user"></i> My Profile</a></li>
                        <li><a href="/recipe_management/auth/logout.php">Logout</a></li>
                    <?php else: ?>
                        <li><a href="/recipe_management/auth/login.php">Login</a></li>
                        <li><a href="/recipe_management/auth/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>
        <div class="container"></div>