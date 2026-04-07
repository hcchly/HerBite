<?php
session_start();
include("db.php");

/* Temporary session for testing until login is finished */
$_SESSION['id'] = 1;
$_SESSION['userType'] = "user";
$_SESSION['firstName'] = "Joud";

if (!isset($_GET['id'])) {
    die("Recipe ID is missing.");
}

$recipeID = $_GET['id'];
$userID = $_SESSION['id'];
$userType = $_SESSION['userType'];

/* Get recipe + creator + category */
$recipeQuery = "SELECT recipe.*, 
                       users.firstName, 
                       users.lastName, 
                       users.photoFileName AS creatorPhoto,
                       recipecategory.categoryName
                FROM recipe
                JOIN users ON recipe.userID = users.id
                LEFT JOIN recipecategory ON recipe.categoryID = recipecategory.id
                WHERE recipe.id = ?";
$stmt = $conn->prepare($recipeQuery);
$stmt->bind_param("i", $recipeID);
$stmt->execute();
$recipeResult = $stmt->get_result();

if ($recipeResult->num_rows == 0) {
    die("Recipe not found.");
}

$recipe = $recipeResult->fetch_assoc();

/* Likes count */
$likesCountQuery = "SELECT COUNT(*) AS totalLikes FROM likes WHERE recipeID = ?";
$stmt = $conn->prepare($likesCountQuery);
$stmt->bind_param("i", $recipeID);
$stmt->execute();
$likesCountResult = $stmt->get_result();
$likesCount = $likesCountResult->fetch_assoc()['totalLikes'];

/* Check if already liked */
$likedQuery = "SELECT * FROM likes WHERE userID = ? AND recipeID = ?";
$stmt = $conn->prepare($likedQuery);
$stmt->bind_param("ii", $userID, $recipeID);
$stmt->execute();
$likedResult = $stmt->get_result();
$alreadyLiked = ($likedResult->num_rows > 0);

/* Check if already favourited */
$favQuery = "SELECT * FROM favourites WHERE userID = ? AND recipeID = ?";
$stmt = $conn->prepare($favQuery);
$stmt->bind_param("ii", $userID, $recipeID);
$stmt->execute();
$favResult = $stmt->get_result();
$alreadyFavourited = ($favResult->num_rows > 0);

/* Check if already reported */
$reportQuery = "SELECT * FROM report WHERE userID = ? AND recipeID = ?";
$stmt = $conn->prepare($reportQuery);
$stmt->bind_param("ii", $userID, $recipeID);
$stmt->execute();
$reportResult = $stmt->get_result();
$alreadyReported = ($reportResult->num_rows > 0);

/* Comments */
$commentsQuery = "SELECT comment.*, users.firstName, users.lastName, users.photoFileName
                  FROM comment
                  JOIN users ON comment.userID = users.id
                  WHERE comment.recipeID = ?
                  ORDER BY comment.date DESC";
$stmt = $conn->prepare($commentsQuery);
$stmt->bind_param("i", $recipeID);
$stmt->execute();
$commentsResult = $stmt->get_result();

/* Show action buttons only if viewer is not creator and not admin */
$canInteract = ($userID != $recipe['userID'] && $userType != "admin");
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>View Recipe | HerBite</title>
  <link rel="stylesheet" href="stylesheet.css">
  <script src="Script.js"></script>
</head>

<body class="user-page view-page admin-page">

  <div class="page">

   <header class="site-header">
    <div class="header-inner">

      <div class="brand">
        <img src="logo.jpg" alt="HerBite Logo">
      </div>

      <div class="brand-title">
        <img src="title.jpg" alt="HerBite Title">
      </div>

      <div class="header-right">
        <div class="welcome">
          Welcome <span class="name"><?php echo $_SESSION['firstName']; ?></span>
        </div>

        <div class="logout">
          <a href="index.html">Sign-out</a>
        </div>
      </div>

    </div>
  </header>

    <!-- MAIN -->
    <main class="page-main">

      <!-- Page Head -->
      <section class="view-head">
        <div class="view-title">
          <h2><?php echo $recipe['name']; ?></h2>
          <p class="muted">Category: <?php echo $recipe['categoryName']; ?></p>
        </div>

        <div class="view-actions">
          <a class="btn-small btn-ghost" href="myRecipes.php">Back</a>

          <?php if ($canInteract) { ?><form action="addLike.php" method="post" style="display:inline;">
              <input type="hidden" name="recipeID" value="<?php echo $recipeID; ?>">
              <button class="btn-small btn-ghost" type="submit" <?php if ($alreadyLiked) echo "disabled"; ?>>
                Like
              </button>
            </form>

            <form action="addFavourite.php" method="post" style="display:inline;">
              <input type="hidden" name="recipeID" value="<?php echo $recipeID; ?>">
              <button class="btn-small btn-ghost" type="submit" <?php if ($alreadyFavourited) echo "disabled"; ?>>
                Add to favourites
              </button>
            </form>

            <form action="addReport.php" method="post" style="display:inline;">
              <input type="hidden" name="recipeID" value="<?php echo $recipeID; ?>">
              <button class="btn-small btn-danger" type="submit" <?php if ($alreadyReported) echo "disabled"; ?>>
                Report
              </button>
            </form>
          <?php } ?>
        </div>
      </section>

      <!-- Overview Card -->
      <section class="admin-card view-card">
        <h2>Recipe Overview</h2>

        <div class="overview-grid">
          <div class="overview-imgWrap">
            <img src="<?php echo $recipe['photoFileName']; ?>" class="overview-img" alt="<?php echo $recipe['name']; ?>">
          </div>

          <div class="overview-info">
            <div class="meta">
              <span class="meta-item">Likes: <?php echo $likesCount; ?></span>
              <span class="meta-item">
                Video: <?php echo !empty($recipe['videoFilePath']) ? "Available" : "Not Available"; ?>
              </span>
            </div>

            <p class="muted">
              <?php echo $recipe['description']; ?>
            </p>

            <?php if (!empty($recipe['videoFilePath'])) { ?>
              <video width="320" controls>
                <source src="<?php echo $recipe['videoFilePath']; ?>" type="video/mp4">
                Your browser does not support the video tag.
              </video>
            <?php } ?>
          </div>
        </div>
      </section>
      
      <!-- Creator Card -->
      <section class="admin-card view-card">
        <h2>Recipe Creator</h2>

        <div class="creator-cell">
          <img src="<?php echo $recipe['creatorPhoto']; ?>" alt="Creator photo" class="creator-avatar square">
          <div>
            <div style="font-weight:800;">
              <?php echo $recipe['firstName'] . " " . $recipe['lastName']; ?>
            </div>
            <div class="muted small">Recipe Creator</div>
          </div>
        </div>
      </section>

      <!-- Build the Bowl -->
      <section class="admin-card view-card">
        <h2>Build the Bowl</h2>
        <p class="muted">Press the button to add ingredients in order.</p>

        <div class="plate-cta">
          <button id="showPlateBtn" class="btn-small btn-primary" type="button">
            Add Ingredients
          </button>
          <div class="muted small">Ingredients will appear one by one.</div>
        </div>

        <div class="build-grid centered">
          <div class="plate-panel plate-pink">

            <div class="plate-stage">
              <img src="Greek-yogurt.png" class="plate-base" alt="Greek Yogurt Bowl">
              <div id="plateLayers" class="plate-layers"></div>
            </div>

            <div class="plate-toolbar">
              <div class="muted small">Selected: <strong id="selectedCount">0</strong></div>

              <div class="toolbar-actions">
                <button id="resetBtn" class="btn-small btn-ghost" type="button">Reset</button>
                <button id="startCookingBtn" class="btn-small btn-primary" type="button">
                  Show Preparation Steps
                </button>
              </div>
            </div>

            <div class="bowl-divider"></div>

            <div class="mini-block">
              <h4>Ingredients List</h4>
              <ul class="list"><li>Greek yoghurt — 1 cup</li>
                <li>Fresh fruits — mixed berries</li>
                <li>Chia seeds — 1 tsp</li>
                <li>Honey — 1 tsp</li>
              </ul>
            </div>

          </div>
        </div>
      </section>

      <section id="cookingSection" class="admin-card view-card" style="display:none;">
        <h2>Preparation Steps</h2>
        <p class="muted">Quick and easy steps.</p>

        <ol class="steps-list">
          <li>Place Greek yoghurt in a bowl.</li>
          <li>Add fresh fruits on top.</li>
          <li>Sprinkle chia seeds evenly.</li>
          <li>Drizzle honey over the bowl.</li>
          <li>Serve immediately and enjoy.</li>
        </ol>
      </section>

      <section class="admin-card view-card">
        <h2>Comments</h2>

        <form class="comment-form" action="addComment.php" method="post">
          <input type="hidden" name="recipeID" value="<?php echo $recipeID; ?>">
          <input type="text" name="comment" placeholder="Write a comment..." required>
          <button class="btn-small btn-primary" type="submit">Add Comment</button>
        </form>

        <div class="comments">
          <?php if ($commentsResult->num_rows > 0) { ?>
            <?php while ($comment = $commentsResult->fetch_assoc()) { ?>
              <div class="comment">
                <div class="comment-head">
                  <img src="<?php echo $comment['photoFileName']; ?>" alt="User photo" class="comment-avatar">
                  <div class="comment-meta">
                    <div class="comment-name">
                      <?php echo $comment['firstName'] . " " . $comment['lastName']; ?>
                    </div>
                    <div class="comment-time muted small">
                      <?php echo $comment['date']; ?>
                    </div>
                  </div>
                </div>
                <div class="comment-body">
                  <?php echo $comment['comment']; ?>
                </div>
              </div>
            <?php } ?>
          <?php } else { ?>
            <p class="muted">No comments yet.</p>
          <?php } ?>
        </div>
      </section>

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
        © 2026 HerBite. All rights reserved.
      </div>
    </footer>

  </div>

</body>
</html>