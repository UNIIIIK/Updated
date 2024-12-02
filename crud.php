<?php
session_start();
include("connection.php");

$connection = new Connection();
$con = $connection->OpenConnection();

// Add Product Logic
if (isset($_POST['add_product'])) {
    $product_name = $_POST['product_name'];
    $category = $_POST['category']; // Foreign key category id
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $availability = $_POST['availability'];
    $date = $_POST['date'];

    $query = "INSERT INTO product_tbl (product_name, category, price, quantity, product_availability, date) 
              VALUES (:product_name, :category, :price, :quantity, :availability, :date)";
    $query_run = $con->prepare($query);

    $data = [
        ':product_name' => $product_name,
        ':category' => $category,  
        ':price' => $price,
        ':quantity' => $quantity,
        ':availability' => $availability,
        ':date' => $date,
    ];

    $query_execute = $query_run->execute($data);

    if ($query_execute) {
        $_SESSION['status'] = "Product Added Successfully";
        header("Location: index.php");
        exit(0);
    } else {
        $_SESSION['status'] = "Product Not Added";
        header("Location: index.php");
        exit(0);
    }
}

// Update Product Logic
if (isset($_POST['update_product'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $availability = $_POST['availability'];
    $date = $_POST['date'];

    // Update query
    $query = "UPDATE product_tbl SET product_name = :product_name, category = :category, price = :price, 
              quantity = :quantity, product_availability = :availability, date = :date WHERE id = :product_id";
    $query_run = $con->prepare($query);

    $data = [
        ':product_name' => $product_name,
        ':category' => $category,
        ':price' => $price,
        ':quantity' => $quantity,
        ':availability' => $availability,
        ':date' => $date,
        ':product_id' => $product_id
    ];

    $query_execute = $query_run->execute($data);

    if ($query_execute) {
        $_SESSION['status'] = "Product Updated Successfully";
        header("Location: index.php");
        exit(0);
    } else {
        $_SESSION['status'] = "Product Not Updated";
        header("Location: index.php");
        exit(0);
    }
}

// Delete Product Logic
if (isset($_POST['delete_product'])) {
    $product_id = $_POST['product_id'];

    // Delete query
    $query = "DELETE FROM product_tbl WHERE id = :product_id";
    $query_run = $con->prepare($query);

    $data = [':product_id' => $product_id];

    $query_execute = $query_run->execute($data);

    if ($query_execute) {
        $_SESSION['status'] = "Product Deleted Successfully";
        header("Location: index.php");
        exit(0);
    } else {
        $_SESSION['status'] = "Product Not Deleted";
        header("Location: index.php");
        exit(0);
    }
}

// Add Category Logic
if (isset($_POST['add_category'])) {
    $cat_name = $_POST['cat_name'];

    $query = "INSERT INTO cat_tbl (cat_name) VALUES (:cat_name)";
    $query_run = $con->prepare($query);

    $data = [
        ':cat_name' => $cat_name,
    ];

    $query_execute = $query_run->execute($data);

    if ($query_execute) {
        $_SESSION['status'] = "Category Added Successfully";
        header("Location: index.php");
        exit(0);
    } else {
        $_SESSION['status'] = "Category Not Added";
        header("Location: index.php");
        exit(0);
    }
}

// Mark Pending Order as Done
if (isset($_POST['mark_order_done'])) {
    $order_id = $_POST['order_id'];

    // Update order status to 'done'
    $query = "UPDATE orders SET status = 'Completed' WHERE id = :order_id";
    $query_run = $con->prepare($query);

    $data = [
        ':order_id' => $order_id,
    ];

    $query_execute = $query_run->execute($data);

    if ($query_execute) {
        $_SESSION['status'] = "Order Marked as Done";
        header("Location: index.php");
        exit(0);
    } else {
        $_SESSION['status'] = "Order Not Updated";
        header("Location: index.php");
        exit(0);
    }
}

// Delete Pending Order Logic
if (isset($_POST['delete_order'])) {
    $order_id = $_POST['order_id'];

    // Delete query for pending orders
    $query = "DELETE FROM orders WHERE id = :order_id";
    $query_run = $con->prepare($query);

    $data = [
        ':order_id' => $order_id,
    ];

    $query_execute = $query_run->execute($data);

    if ($query_execute) {
        $_SESSION['status'] = "Order Deleted Successfully";
        header("Location: index.php");
        exit(0);
    } else {
        $_SESSION['status'] = "Order Not Deleted";
        header("Location: index.php");
        exit(0);
    }
}
?>
