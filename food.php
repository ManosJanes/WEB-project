<?php

include 'access.php';

// Start the session
session_start();

// Check if the user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    // Redirect to the login page or display an error message
    header("Location: demo.php?block=1");
    exit();
}

// Read the existing categories from items.json
$jsonFileItems = 'items.json';
$jsonDataItems = file_get_contents($jsonFileItems);
$itemsData = json_decode($jsonDataItems, true);
$categories = isset($itemsData['items']) ? array_unique(array_column($itemsData['items'], 'category')) : [];

// Read the existing categories from categories.json
$jsonFileCategories = 'categories.json';
$jsonDataCategories = file_get_contents($jsonFileCategories);
$categoriesData = json_decode($jsonDataCategories, true);
$categoriesFromJson = isset($categoriesData['categories']) ? $categoriesData['categories'] : [];

// Merge the categories from items.json and categories.json
$allCategories = array_unique(array_merge($categories, $categoriesFromJson));

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="items.css">
  <title>Food</title>
  <style>
    body {
      font-family: Arial, sans-serif;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: left;
    }

    th {
      background-color: #f2f2f2;
    }

    input, select {
      width: 100%;
      padding: 8px;
      box-sizing: border-box;
      margin-bottom: 10px;
    }

    .button-container {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }

    .action-button {
      background-color: #4caf50;
      color: white;
      padding: 10px 15px;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      font-size: 14px;
    }

    .add-item-container {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
    }

    .add-category-container {
      display: flex;
      gap: 10px;
      align-items: center;
    }
  </style>
</head>
<body>
  <table>
    <thead>
      <tr>
        <th>Name</th>
        <th>Category</th>
        <th>Details</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody id="data-output">
      <!-- Products from the PHP file will be inserted here -->
      <?php
          // Inside the addItem function
        if (isset($itemsData['items'])) {
          foreach ($itemsData['items'] as $item) {
              $detailsHTML = '';

              // Check if 'name' key is set before accessing it
              if (isset($item['name'])) {
                  // Check if 'details' key is set and is an array before iterating over it
                  if (isset($item['details']) && is_array($item['details'])) {
                      foreach ($item['details'] as $detail) {
                          $detailsHTML .= $detail['detail_name'] . ': ' . $detail['detail_value'] . "<br>";
                      }
                  }

                  echo "
                  <tr>
                      <td>{$item['name']}</td>
                      <td>{$item['category']}</td>
                      <td>{$detailsHTML}</td>
                      <td>
                          <button onclick=\"deleteItem('{$item['id']}')\" class=\"action-button\">Delete</button>
                          <button onclick=\"editItem('{$item['id']}')\" class=\"action-button\">Edit</button>    
                      </td>
                  </tr>";
              }
          }
        }

      ?>          
    </tbody>
  </table>

  <div class="add-item-container">
    <div style="flex: 2;">
      <label for="newName">Name:</label>
      <input type="text" id="newName" placeholder="Name">
    </div>
    <div style="flex: 2;">
      <label for="newCategory">Category:</label>
      <select id="newCategory">
        <?php
        foreach ($allCategories as $category) {
            echo "<option value=\"$category\">$category</option>";
        }
        ?>
      </select>
    </div>
    <div id="details-container"></div>
    <div style="flex: 2;">
      <label for="newDetailName">Detail Name:</label>
      <input type="text" id="newDetailName" placeholder="Detail Name">
    </div>
    <div style="flex: 2;">
      <label for="newDetailValue">Detail Value:</label>
      <input type="text" id="newDetailValue" placeholder="Detail Value">
    </div>
    <div>
      <button onclick="addDetail()" class="action-button">Add Detail</button>
      <input type="hidden" id="hiddenDetailsInput" name="details" value="">
    </div>
  </div>

  <div class="add-category-container">
    <div style="flex: 1;">
      <label for="newCategoryDirect">New Category:</label>
      <input type="text" id="newCategoryDirect" placeholder="New Category">
    </div>
    <div>
      <button onclick="addCategoryDirect()" class="action-button">Add Category Directly</button>
    </div>
  </div>


  <div class="button-container">
    <button onclick="addItem()" class="action-button">Add Item</button>
    <button onclick="window.location.href = 'admin.php';" class="action-button">GO BACK</button>
    <button onclick="window.location.href = 'other_inv.php';" class="action-button">Show other inventories</button>
  </div>

  <script src="admin.js"></script>
  <script>      
      document.addEventListener("DOMContentLoaded", function () {
        main();
        loadCategories();
      });
  </script>
</body>
</html>