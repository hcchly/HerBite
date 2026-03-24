<?php
include "db.php";
session_start();

$user_id = 1; // مؤقت

if (isset($_GET['recipeID'])) {
    $recipeID = (int)$_GET['recipeID'];

    $sql = "DELETE FROM Favourites 
            WHERE userID = $user_id AND recipeID = $recipeID";
    mysqli_query($conn, $sql);
}

header("Location: user.php");
exit();
?>
