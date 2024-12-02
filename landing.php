<?php
session_start();
include('connection.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$connection = new Connection();
$pdo = $connection->OpenConnection();

// Fetch products for display
$query = "SELECT * FROM product_tbl";
$stmt = $pdo->prepare($query);
$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Santiago's Store</h2>

    <div class="row">
        <?php foreach ($products as $product): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($product['product_name']) ?></h5>
                        <p class="card-text">Price: $<?= htmlspecialchars($product['price']) ?></p>
                        <p class="card-text">Quantity: <?= htmlspecialchars($product['quantity']) ?></p>
                        <form onsubmit="addToCart(event, <?= $product['id'] ?>)">
                            <input type="number" id="quantity-<?= $product['id'] ?>" class="form-control mb-2" min="1" max="<?= $product['quantity'] ?>" required>
                            <button type="button" class="btn btn-primary" onclick="addToCart(event, <?= $product['id'] ?>)">Add to Cart</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <a href="cart.php" class="btn btn-success mt-3">View Cart</a>
</div>

<script>
function addToCart(event, productId) {
    event.preventDefault();
    const quantity = document.getElementById('quantity-' + productId).value;

    fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId, quantity: quantity })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert("Item added to cart successfully.");
        } else {
            alert("Failed to add item to cart.");
        }
    })
    .catch(error => console.error('Error:', error));
}



</script>
    <!-- Logout Button for Users -->
    <div class="d-flex justify-content-end mb-3">
        <a href="logout.php" class="btn btn-outline-danger">Logout</a>
    </div>
</body>
</html>
