<?php
session_start();
include('connection.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

$connection = new Connection();
$pdo = $connection->OpenConnection();

$cart_items = $_SESSION['cart'] ?? [];
$total_price = 0;

// Remove item from cart
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['remove_item'])) {
    $remove_id = $_POST['product_id'];
    unset($_SESSION['cart'][$remove_id]);
    header("Location: cart.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cart</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Imong Cart</h2>

    <?php if (!empty($cart_items)): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cart_items as $product_id => $quantity): ?>
                    <?php
                    $stmt = $pdo->prepare("SELECT * FROM product_tbl WHERE id = :id");
                    $stmt->execute([':id' => $product_id]);
                    $product = $stmt->fetch(PDO::FETCH_ASSOC);
                    $product_total = $product['price'] * $quantity;
                    $total_price += $product_total;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($product['product_name']) ?></td>
                        <td>$<?= number_format($product['price'], 2) ?></td>
                        <td><?= $quantity ?></td>
                        <td>$<?= number_format($product_total, 2) ?></td>
                        <td>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="product_id" value="<?= $product_id ?>">
                                <button type="submit" name="remove_item" class="btn btn-danger btn-sm">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="3">Total</th>
                    <th>$<?= number_format($total_price, 2) ?></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
        <a href="checkout.php" class="btn btn-primary">Proceed to Checkout</a>
    <?php else: ?>
        <p>Your cart is empty.</p>
    <?php endif; ?>
</div>
</body>
</html>
