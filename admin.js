function loadCategories() {
  // Fetch categories from categories.json
  fetch("categories.json")
      .then(function (response) {
          return response.json();
      })
      .then(function (data) {
          const categoryDropdown = document.getElementById('newCategory');

          // Clear existing options
          categoryDropdown.innerHTML = '';

          // Add new options from the fetched data
          if (data.categories) {
              data.categories.forEach(function (category) {
                  const option = document.createElement('option');
                  option.value = category;
                  option.text = category;
                  categoryDropdown.add(option);
              });
          }
      });
}

function addCategoryDirect() {
  const newCategoryDirect = document.getElementById('newCategoryDirect').value;

  if (newCategoryDirect) {
      // Make a fetch request to add the new category directly
      fetch("addCategory.php", {
          method: "POST",
          headers: {
              "Content-Type": "application/x-www-form-urlencoded",
          },
          body: `action=addCategory&category=${encodeURIComponent(newCategoryDirect)}`,
      })
          .then(function (response) {
              return response.json();
          })
          .then(function (data) {
              if (data.success) {
                  // Optionally, update the DOM or provide user feedback
                  alert('Category added successfully.');

                  // Update the categories dropdown or perform other actions as needed
                  loadCategories();
              } else {
                  alert(`Failed to add category. ${data.message}`);
              }
          })
          .catch(function (error) {
              console.error("There was a problem with the fetch operation:", error);
              alert('Failed to add category. Please try again.');
          });
  } else {
      alert('Please fill in the New Category field.');
  }
}

// Function to load existing items from items.json
function loadData() {
  fetch("items.json")
    .then(function (response) {
      return response.json();
    })
    .then(function (itemsData) {
      const dataOutput = document.getElementById("data-output");

      // Clear existing items
      dataOutput.innerHTML = "";

      // Add new items from the fetched data
      if (itemsData.items) {
        itemsData.items.forEach(function (item) {
          const detailsHTML = `<td>${item.name}</td><td>${item.category}</td><td>${generateDetailsHTML(
            item.details
          )}</td><td><button onclick="deleteItem('${item.id}')" class="action-button">Delete</button><button onclick="editItem('${item.id}')" class="action-button">Edit</button></td>`;
          const newRow = document.createElement("tr");
          newRow.innerHTML = detailsHTML;
          newRow.id = item.id;
          dataOutput.appendChild(newRow);
        });
      }
    });
}

// Function to generate HTML for item details
function generateDetailsHTML(details) {
  let detailsHTML = "";
  details.forEach(function (detail) {
    detailsHTML += `${detail.detail_name}: ${detail.detail_value}<br>`;
  });
  return detailsHTML;
}

function deleteItem(itemId) {
  fetch("update.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({ action: "deleteItem", deletedItemId: itemId }),
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("Network response was not ok");
      }
      return response.json();
    })
    .then(function (data) {
      console.log(data); // Log the response for debugging

      if (data.success) {
        // Remove the deleted item from the DOM
        removeItemFromDOM(itemId);
        alert('Item deleted successfully.');
      } else {
        alert(`Failed to delete item. ${data.message}`);
      }
    })
    .catch(function (error) {
      console.error("There was a problem with the fetch operation:", error);
      alert('Failed to delete item. Please try again.');
    });
}

// Helper function to remove the deleted item from the DOM
function removeItemFromDOM(itemId) {
  const deletedItem = document.getElementById(itemId);
  if (deletedItem) {
    deletedItem.remove();
  }
}

// Declare a global array to store details
var detailsArray = [];


document.addEventListener("DOMContentLoaded", function () {
  main();
  loadCategories();
});

function addDetail() {
  // Get input values
  var detailName = document.getElementById("newDetailName").value;
  var detailValue = document.getElementById("newDetailValue").value;

  // Create a new detail object
  var newDetail = {
    detail_name: detailName,
    detail_value: detailValue,
  };

  // Add the new detail to the detailsArray
  storeDetails(newDetail);

  // Add the new detail to the details-container
  var detailsContainer = document.getElementById("details-container");

  // Create a new div element to hold the detail information
  var detailDiv = document.createElement("div");
  detailDiv.innerHTML = `${detailName}: ${detailValue}`;
  var removeButton = document.createElement("button");
  removeButton.textContent = "Remove";
  removeButton.onclick = function() {
    removeDetail(removeButton);
  };
  detailDiv.appendChild(removeButton);

  // Append the detailDiv to the detailsContainer
  detailsContainer.appendChild(detailDiv);

  // Clear the input fields
  document.getElementById("newDetailName").value = "";
  document.getElementById("newDetailValue").value = "";
}

function addItem() {
  var name = document.getElementById("newName").value;
  var category = document.getElementById("newCategory").value;

  // Get details from the global array
  var details = detailsArray;

  var data = {
    action: "addItem",
    newName: name,
    newCategory: category,
    details: details,
  };

  // Use fetch to send data to the server
  fetch("update.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Item added successfully!");
        // Optionally, you can reset the form or perform other actions
        // Clear the details array after adding the item
        detailsArray = [];
        // Clear the input fields
        document.getElementById("newName").value = "";
        document.getElementById("newCategory").value = "";
        document.getElementById("details-container").innerHTML = "";
        document.getElementById("newDetailName").value = "";
        document.getElementById("newDetailValue").value = "";
        // Refresh the data on the page
        loadData();
      } else {
        alert("Failed to add item. Please try again.");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("An error occurred. Please try again.");
    });
}

// Function to store details in the detailsArray
function storeDetails(newDetail) {
  detailsArray.push(newDetail);
}


// Function to remove a dynamically added detail
function removeDetail(button) {
  button.parentElement.remove();
}