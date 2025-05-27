USE recipe_management;

-- Add category column if it doesn't exist
ALTER TABLE recipes
ADD COLUMN IF NOT EXISTS category ENUM('appetizer', 'main course', 'dessert') NOT NULL DEFAULT 'main course' AFTER title; 