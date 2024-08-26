<!DOCTYPE html>
<html lang="en">
<head>
	<!-- Setting the pages character encoding -->
	<meta charset="UTF-8">

	<!-- The meta viewport will scale my content to any device width -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	 <!-- Link to my stylesheet -->
	 <link rel="stylesheet" href="items.css">
	<title>Pharmacies</title>
</head>
<body>
   <table>
      <thead>
         <tr>
            <th>Type</th>
            <th>Item</th>
            <th>Brand</th>
         </tr>
      </thead>
      <tbody id="data-output">
         <!-- Prodcuts from javascript file in here. -->
      </tbody>
      <button onclick="window.location.href = 'admin.php';">GO BACK</button>
   </table>
 
   <script src="pharmacies.js"></script> 

</html>