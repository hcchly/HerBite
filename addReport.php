<?php
session_start();
include("db.php");

$_SESSION['id'] = 1;
$_SESSION['userType'] = "user";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $recipeID = $_POST['recipeID'];
    $userID = $_SESSION['id'];

    $checkQuery = "SELECT * FROM report WHERE userID = ? AND recipeID = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $userID, $recipeID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        $query = "INSERT INTO report (userID, recipeID) VALUES (?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $userID, $recipeID);
        $stmt->execute();
    }

    header("Location: viewRecipe.php?id=" . $recipeID);
    exit();
}
?>