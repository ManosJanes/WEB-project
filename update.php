<?php

$jsonPayload = file_get_contents('php://input');
$data = json_decode($jsonPayload, true);

// If the payload is not JSON, handle form data
if (!$data) {
    $data = $_POST;
}

// Check if the action is set in the data
if (isset($data['action'])) {
    $action = $data['action'];

    // Call the appropriate function based on the action
    switch ($action) {
        case 'addItem':
            addItem($data);
            break;

        case 'deleteItem':
            deleteItem($data['deletedItemId']);
            break;

        case 'addCategory':
            addCategory($data['newCategory']);
            break;

        default:
            // Handle other cases or provide an error response
            header('HTTP/1.1 400 Bad Request');
            echo json_encode(['error' => 'Invalid action.']);
            break;
    }
} else {
    // Handle other cases or provide an error response
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'Invalid request.']);
}

function addItem($postData) {
    // Get the input data
    $name = $postData['newName'];
    $category = $postData['newCategory'];
    $details = $postData['details'];

    // Generate a unique ID (you may need to implement your own ID generation logic)
    $id = uniqid();

    // Create a new item with details as an array
    $newItem = [
        'id' => $id,
        'name' => $name,
        'category' => $category,
        'details' => $details  // Include the details field in the new item
    ];

    // Read the existing items from items.json
    $jsonFileItems = 'items.json';
    $jsonDataItems = file_get_contents($jsonFileItems);
    $itemsData = json_decode($jsonDataItems, true);

    // If the file doesn't exist or is empty, initialize the items array
    if (!$itemsData || !isset($itemsData['items'])) {
        $itemsData = ['items' => []];
    }

    // Add the new item to the existing items
    $itemsData['items'][] = $newItem;

    // Save the updated data back to items.json
    file_put_contents($jsonFileItems, json_encode($itemsData, JSON_PRETTY_PRINT));

    // Return a success response or handle as needed
    echo json_encode(['success' => true, 'id' => $id]);
}



function updateItemCategories() {
    $jsonFileItems = 'items.json';
    $jsonFileCategories = 'categories.json';

    // Read items data
    $jsonDataItems = file_get_contents($jsonFileItems);
    $itemsData = json_decode($jsonDataItems, true);

    // Read categories data
    $jsonDataCategories = file_get_contents($jsonFileCategories);
    $categoriesData = json_decode($jsonDataCategories, true);
    $validCategories = isset($categoriesData['categories']) ? $categoriesData['categories'] : [];

    // Update categories for each item
    foreach ($itemsData['items'] as $key => $item) {
        $itemCategory = $item['category'];
        // Check if the item category is valid
        if (!in_array($itemCategory, $validCategories)) {
            // If not, update it to the first valid category
            $itemsData['items'][$key]['category'] = reset($validCategories);
        }
    }

    // Save the updated data back to items.json
    file_put_contents($jsonFileItems, json_encode($itemsData, JSON_PRETTY_PRINT));
}

function updateItems($newItem) {
    // Log received data for debugging
    error_log('Received Data: ' . print_r($newItem, true));

    $jsonFileItems = 'items.json';
    $jsonDataItems = file_get_contents($jsonFileItems);
    $itemsData = json_decode($jsonDataItems, true);

    // Generate a unique ID (you may need to implement your own ID generation logic)
    $newItem['id'] = uniqid();

    $newItem['details'] = is_array($newItem['details']) ? $newItem['details'] : json_decode($newItem['details'], true);

    // Append the new details to the existing details
    if (isset($newItem['details']) && is_array($newItem['details'])) {
        $itemsData['items'][0]['details'] = array_merge($itemsData['items'][0]['details'], $newItem['details']);
    }

    // Save the updated data back to items.json
    file_put_contents($jsonFileItems, json_encode($itemsData, JSON_PRETTY_PRINT));

    // Update categories to ensure consistency
    updateItemCategories();

    echo json_encode(['success' => true, 'id' => $newItem['id']]);

}

function deleteItem($deletedItemId) {
    $jsonFileItems = 'items.json';
    $jsonDataItems = file_get_contents($jsonFileItems);
    $itemsData = json_decode($jsonDataItems, true);

    // Find and remove the item with the given ID
    foreach ($itemsData['items'] as $key => $item) {
        if ($item['id'] === $deletedItemId) {
            unset($itemsData['items'][$key]);
            break;
        }
    }

    // Save the updated data back to items.json
    file_put_contents($jsonFileItems, json_encode($itemsData, JSON_PRETTY_PRINT));

    echo json_encode(['success' => true]);
}

function addCategory($newCategory) {
    $jsonFileCategories = 'categories.json';
    $jsonDataCategories = file_get_contents($jsonFileCategories);
    $categoriesData = json_decode($jsonDataCategories, true);

    // Add the new category to the existing data
    $categoriesData['categories'][] = $newCategory;

    // Remove duplicates to ensure uniqueness
    $categoriesData['categories'] = array_unique($categoriesData['categories']);

    // Save the updated data back to categories.json
    file_put_contents($jsonFileCategories, json_encode($categoriesData, JSON_PRETTY_PRINT));

    echo json_encode(['success' => true, 'message' => 'Category added successfully.']);
}

?>
