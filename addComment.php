<?php
session_start();
include("db.php");

$_SESSION['id'] = 1;
$_SESSION['userType'] = "user";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipeID = $_POST['recipeID'];
    $userID = $_SESSION['id'];
    $commentText = $_POST['comment'];
    $date = date("Y-m-d H:i:s");

    $query = "INSERT INTO comment (recipeID, userID, comment, date) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iiss", $recipeID, $userID, $commentText, $date);
    $stmt->execute();

    header("Location: viewRecipe.php?id=" . $recipeID);
    exit();
}
?>