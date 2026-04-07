<?php
session_start();
include("db.php");

/* Temporary session for testing until login is finished */
$_SESSION['id'] = 1;
$_SESSION['userType'] = "admin";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $reportID = $_POST['reportID'];
    $creatorID = $_POST['creatorID'];
    $action = $_POST['action'];

    if ($action == "dismiss") {
        $deleteReport = "DELETE FROM report WHERE id = ?";
        $stmt = $conn->prepare($deleteReport);
        $stmt->bind_param("i", $reportID);
        $stmt->execute();
    }

    elseif ($action == "block") {

        /* Get creator info */
        $userQuery = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($userQuery);
        $stmt->bind_param("i", $creatorID);
        $stmt->execute();
        $userResult = $stmt->get_result();

        if ($userResult->num_rows > 0) {
            $user = $userResult->fetch_assoc();

            /* Add user to blockeduser */
            $insertBlocked = "INSERT INTO blockeduser (firstName, lastName, emailAddress)
                              VALUES (?, ?, ?)";
            $stmt = $conn->prepare($insertBlocked);
            $stmt->bind_param("sss", $user['firstName'], $user['lastName'], $user['emailAddress']);
            $stmt->execute();
        }

        /* Delete reports of this user's recipes */
        $deleteReports = "DELETE report FROM report
                          JOIN recipe ON report.recipeID = recipe.id
                          WHERE recipe.userID = ?";
        $stmt = $conn->prepare($deleteReports);
        $stmt->bind_param("i", $creatorID);
        $stmt->execute();

        /* Delete likes of this user's recipes */
        $deleteLikes = "DELETE likes FROM likes
                        JOIN recipe ON likes.recipeID = recipe.id
                        WHERE recipe.userID = ?";
        $stmt = $conn->prepare($deleteLikes);
        $stmt->bind_param("i", $creatorID);
        $stmt->execute();

        /* Delete favourites of this user's recipes */
        $deleteFavs = "DELETE favourites FROM favourites
                       JOIN recipe ON favourites.recipeID = recipe.id
                       WHERE recipe.userID = ?";
        $stmt = $conn->prepare($deleteFavs);
        $stmt->bind_param("i", $creatorID);
        $stmt->execute();

        /* Delete comments of this user's recipes */
        $deleteComments = "DELETE comment FROM comment
                           JOIN recipe ON comment.recipeID = recipe.id
                           WHERE recipe.userID = ?";
        $stmt = $conn->prepare($deleteComments);
        $stmt->bind_param("i", $creatorID);
        $stmt->execute();

        /* Delete recipes */
        $deleteRecipes = "DELETE FROM recipe WHERE userID = ?";
        $stmt = $conn->prepare($deleteRecipes);
        $stmt->bind_param("i", $creatorID);
        $stmt->execute();

        /* Delete user */
        $deleteUser = "DELETE FROM users WHERE id = ?";
        $stmt = $conn->prepare($deleteUser);
        $stmt->bind_param("i", $creatorID);
        $stmt->execute();
    }

    header("Location: admin.php");
    exit();
}
?>