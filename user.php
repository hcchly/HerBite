<?php

include "db.php";

$user_id = 1;

$selectedCategory = isset($_POST['categoryID']) ? (int) $_POST['categoryID'] : 0;

$stmt_user = $conn->prepare("SELECT * FROM User WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();

if (!$user) {
    die("User not found");
}

$stmt_total_recipes = $conn->prepare("SELECT COUNT(*) AS totalRecipes FROM Recipe WHERE userID = ?");
$stmt_total_recipes->bind_param("i", $user_id);
$stmt_total_recipes->execute();
$totalRecipes = $stmt_total_recipes->get_result()->fetch_assoc()['totalRecipes'] ?? 0;

$stmt_total_likes = $conn->prepare("
    SELECT COUNT(Likes.recipeID) AS totalLikes
    FROM Recipe
    LEFT JOIN Likes ON Recipe.id = Likes.recipeID
    WHERE Recipe.userID = ?
");
$stmt_total_likes->bind_param("i", $user_id);
$stmt_total_likes->execute();
$totalLikes = $stmt_total_likes->get_result()->fetch_assoc()['totalLikes'] ?? 0;

$result_categories = $conn->query("SELECT id, categoryName FROM RecipeCategory GROUP BY id, categoryName ORDER BY id ASC");

if ($selectedCategory > 0) {
    $stmt_recipes = $conn->prepare("
        SELECT Recipe.id,
               Recipe.name,
               Recipe.photoFileName AS recipePhoto,
               User.firstName,
               User.lastName,
               User.photoFileName AS creatorPhoto,
               RecipeCategory.categoryName,
               COUNT(Likes.userID) AS likesCount
        FROM Recipe
        JOIN User ON Recipe.userID = User.id
        JOIN RecipeCategory ON Recipe.categoryID = RecipeCategory.id
        LEFT JOIN Likes ON Recipe.id = Likes.recipeID
        WHERE Recipe.categoryID = ?
        GROUP BY Recipe.id, Recipe.name, Recipe.photoFileName, User.firstName, User.lastName, User.photoFileName, RecipeCategory.categoryName
        ORDER BY Recipe.id ASC
    ");
    $stmt_recipes->bind_param("i", $selectedCategory);
    $stmt_recipes->execute();
    $result_recipes = $stmt_recipes->get_result();
} else {
    $result_recipes = $conn->query("
        SELECT Recipe.id,
               Recipe.name,
               Recipe.photoFileName AS recipePhoto,
               User.firstName,
               User.lastName,
               User.photoFileName AS creatorPhoto,
               RecipeCategory.categoryName,
               COUNT(Likes.userID) AS likesCount
        FROM Recipe
        JOIN User ON Recipe.userID = User.id
        JOIN RecipeCategory ON Recipe.categoryID = RecipeCategory.id
        LEFT JOIN Likes ON Recipe.id = Likes.recipeID
        GROUP BY Recipe.id, Recipe.name, Recipe.photoFileName, User.firstName, User.lastName, User.photoFileName, RecipeCategory.categoryName
        ORDER BY Recipe.id ASC
    ");
}

$stmt_favourites = $conn->prepare("
    SELECT Recipe.id,
           Recipe.name,
           Recipe.photoFileName
    FROM Favourites
    JOIN Recipe ON Favourites.recipeID = Recipe.id
    WHERE Favourites.userID = ?
    ORDER BY Recipe.id ASC
");
$stmt_favourites->bind_param("i", $user_id);
$stmt_favourites->execute();
$result_favourites = $stmt_favourites->get_result();

$userPhoto = !empty($user['photoFileName']) ? $user['photoFileName'] : 'avatar.png';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>HerBite | User Page</title>
  <link rel="stylesheet" href="stylesheet.css" />
  <script src="Script.js" defer></script>
</head>
<body class="user-page header-admin">
  <main class="page">
    <header class="site-header">
      <div class="header-inner">
        <a href="index.html" class="home-link" aria-label="Go to home">
          <img src="home.PNG" alt="Home">
        </a>

        <div class="brand">
          <img src="logo.jpg" alt="HerBite Logo">
        </div>

        <div class="brand-title">
          <img src="title.jpg" alt="HerBite Title">
        </div>

        <div class="header-right">
          <div class="welcome">
            Welcome <span class="name"><?php echo htmlspecialchars($user['firstName']); ?></span>
          </div>
        </div>

        <div class="logout">
          <a href="index.html">Sign-out</a>
        </div>
      </div>
    </header>

    <div class="page-main">
      <section class="card">
        <div class="big-box">
          <div class="bar-left">
            <h2 class="bar-title">My Information</h2>
            <p><strong>Name :</strong> <?php echo htmlspecialchars($user['firstName'] . ' ' . $user['lastName']); ?></p>
            <p><strong>Email :</strong> <?php echo htmlspecialchars($user['emailAddress']); ?></p>
          </div>

          <div class="bar-right">
            <div class="user-photo-box">
              <img src="<?php echo htmlspecialchars($userPhoto); ?>" alt="User photo">
            </div>
          </div>
        </div>
      </section>

      <section class="card">
        <div class="big-box">
          <div class="bar-left">
            <h2 class="bar-title">
              <a href="myRecipes.php">My Recipes</a>
            </h2>
            <p><strong>Total Recipes:</strong> <?php echo (int) $totalRecipes; ?></p>
            <p><strong>Total Likes:</strong> <?php echo (int) $totalLikes; ?></p>
          </div>

          <div class="bar-right"></div>
        </div>
      </section>

      <section class="box">
        <div class="section-head">
          <h2>All Available Recipes</h2>

          <form method="POST" action="user.php" class="filter">
            <select name="categoryID">
              <option value="">All Categories</option>
              <?php while ($category = mysqli_fetch_assoc($result_categories)) { ?>
                <option value="<?php echo (int) $category['id']; ?>" <?php echo ($selectedCategory === (int) $category['id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($category['categoryName']); ?>
                </option>
              <?php } ?>
            </select>
            <button class="btn-filter" type="submit">Filter</button>
          </form>
        </div>

        <?php if ($result_recipes && mysqli_num_rows($result_recipes) > 0) { ?>
          <table class="recipes-table">
            <thead>
              <tr>
                <th>Recipe Name</th>
                <th>Recipe Photo</th>
                <th>Recipe Creator</th>
                <th>Number of Likes</th>
                <th>Category</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_assoc($result_recipes)) { ?>
                <tr>
                  <td>
                    <a href="viewRecipes.php?id=<?php echo (int) $row['id']; ?>">
                      <?php echo htmlspecialchars($row['name']); ?>
                    </a>
                  </td>
                  <td>
                    <img src="<?php echo htmlspecialchars($row['recipePhoto']); ?>" class="recipe-img" alt="Recipe Photo">
                  </td>
                  <td>
                      <img src="<?php echo htmlspecialchars($row['creatorPhoto']); ?>" class="creator-avatar" alt="Creator Photo">
                    <?php echo htmlspecialchars($row['firstName'] . ' ' . $row['lastName']); ?>
                  </td>
                 
                  <td>
                    <?php echo (int) $row['likesCount']; ?>
                  </td>
                  <td>
                    <?php echo htmlspecialchars($row['categoryName']); ?>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        <?php } else { ?>
          <p>No recipes found in this category.</p>
        <?php } ?>
      </section>

      <section class="box">
        <h2>My Favourite Recipes ♥️</h2>

        <?php if ($result_favourites && mysqli_num_rows($result_favourites) > 0) { ?>
          <table class="favorites-table">
            <thead>
              <tr>
                <th>Recipe Name</th>
                <th>Recipe Photo</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php while ($fav = mysqli_fetch_assoc($result_favourites)) { ?>
                <tr>
                  <td>
                    <a href="viewRecipes.php?id=<?php echo (int) $fav['id']; ?>">
                      <?php echo htmlspecialchars($fav['name']); ?>
                    </a>
                  </td>
                  <td>
                    <img src="<?php echo htmlspecialchars($fav['photoFileName']); ?>" class="recipe-img" alt="Recipe Photo">
                  </td>
                  <td>
                    <a class="remove-link" href="removeFavourite.php?recipeID=<?php echo (int) $fav['id']; ?>">Remove</a>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        <?php } else { ?>
          <p>You have no favourite recipes.</p>
        <?php } ?>
      </section>
    </div>
  </main>

  <footer class="site-footer">
    <div class="footer-inner">
      <div class="footer-col">
        <h4>Services</h4>
        <p>Healthy Recipes</p>
        <p>Quick Meals</p>
        <p>Balanced Plates</p>
      </div>

      <div class="footer-col">
        <h4>Locations</h4>
        <p>Riyadh</p>
        <p>Jeddah</p>
        <p>Dammam</p>
      </div>

      <div class="footer-col">
        <h4>Contact Us</h4>
        <p>+966 5X XXX XXXX</p>
        <p>herbite@email.com</p>
        <p>@HerBite</p>
      </div>
    </div>

    <div class="footer-bottom">
      ©️ 2026 HerBite. All rights reserved.
    </div>
  </footer>
</body>
</html>
