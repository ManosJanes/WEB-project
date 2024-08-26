<?php
// addCategories.php

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Check if the action is to add a category
    if (isset($_POST['action']) && $_POST['action'] === 'addCategory') {
        $newCategory = $_POST['category'];

        if ($newCategory) {
            // Read existing categories
            $jsonFile = 'categories.json';
            $jsonData = file_get_contents($jsonFile);
            $data = json_decode($jsonData, true);

            // Check if the category already exists
            if (!in_array($newCategory, $data['categories'])) {
                $data['categories'][] = $newCategory;

                // Save the modified data back to the JSON file
                file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));

                // Send a JSON response indicating success
                echo json_encode(['success' => true, 'categories' => $data['categories']]);
                exit();
            } else {
                // Send a JSON response indicating that the category already exists
                echo json_encode(['success' => false, 'message' => 'Category already exists.']);
                exit();
            }
        } else {
            // Send a JSON response indicating that the category field is empty
            echo json_encode(['success' => false, 'message' => 'Please fill in the category field.']);
            exit();
        }
    }
}
?>
