fetch("items.json")
  .then(function (response) {
    return response.json();
  })
  .then(function (data) {
    let placeholder = document.querySelector("#data-output");
    let out = "";


    // Display pharmacy items
    if (data.pharmacies) {

      for (let pharmacy of data.pharmacies) {
        out += `
          <tr>
            <td>${pharmacy.type}</td>
            <td>${pharmacy.item}</td>
            <td>${pharmacy.brand}</td>
          </tr>
        `;
      } 
    } 

    placeholder.innerHTML = out;
  })
  .catch(function (error) {
    console.error('Error fetching data:', error);
  });