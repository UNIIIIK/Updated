<?php
session_start();
include('filter.php');
include('connection.php');

$connection = new Connection();
$pdo = $connection->OpenConnection();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container border border-dark mt-3">
    <br>
    <!-- Filter Form -->
    <form class="row g-3" method="POST" action="index.php">
        <div class="col-md-6">
            <label for="start_date" class="form-label">Start Date:</label>
            <input type="date" name="start_date" class="form-control">
        </div>
        <div class="col-md-6">
            <label for="end_date" class="form-label">End Date:</label>
            <input type="date" name="end_date" class="form-control">
        </div>
        <div class="col-md-6">
            <label for="product_name" class="form-label">Product Name</label>
            <input type="text" class="form-control" name="product_name">
        </div>
        <div class="col-md-6">
            <label for="category" class="form-label">Category</label>
            <select class="form-select" name="category">
                <option value="">All</option>
                <?php
                // Fetch categories for filter dropdown
                $stmt = $pdo->query("SELECT * FROM cat_tbl");
                $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
                foreach ($categories as $cat) {
                    echo "<option value='{$cat['cat_id']}'>{$cat['cat_name']}</option>";
                }
                ?>
            </select>
        </div>
        <div class="col-md-6">
            <label for="product_availability" class="form-label">Product Availability</label>
            <select class="form-select" name="product_availability">
                <option value="">All</option>
                <option value="In Stock">In stock</option>
                <option value="Out of Stock">Out of stock</option>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" name="search" class="btn btn-outline-primary">Filter</button>
        </div>
    </form>
    <br>
</div>

<!-- Add Product and Add Category Buttons -->
<div class="container">
    <div class="col-12 mt-3 d-flex justify-content-end">
        <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#addModal">Add Product</button>
        <button type="button" class="btn btn-outline-secondary ms-2" data-bs-toggle="modal" data-bs-target="#addCategoryModal">Add Category</button>
    </div>

    <!-- Bootstrap Table for Product List -->
    <table class="table table-dark table-hover mt-3">
        <thead>
            <tr>
                <th scope="col">Id</th>
                <th scope="col">Product Name</th>
                <th scope="col">Category</th>
                <th scope="col">Price</th>
                <th scope="col">Quantity</th>
                <th scope="col">Product Availability</th>
                <th scope="col">Date</th>
                <th scope="col">Action</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Use the filter logic to show filtered results, or fetch all products
            $sql = "SELECT p.*, c.cat_name FROM product_tbl p INNER JOIN cat_tbl c ON p.category = c.cat_id";

            if (isset($_POST['search'])) {
                $conditions = [];
                $params = [];

                if (!empty($_POST['product_name'])) {
                    $conditions[] = "p.product_name LIKE :product_name";
                    $params[':product_name'] = "%" . $_POST['product_name'] . "%";
                }

                if (!empty($_POST['category'])) {
                    $conditions[] = "p.category = :category";
                    $params[':category'] = $_POST['category'];
                }

                if (!empty($_POST['product_availability'])) {
                    $conditions[] = "p.product_availability = :availability";
                    $params[':availability'] = $_POST['product_availability'];
                }

                if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
                    $conditions[] = "p.date BETWEEN :start_date AND :end_date";
                    $params[':start_date'] = $_POST['start_date'];
                    $params[':end_date'] = $_POST['end_date'];
                }

                if ($conditions) {
                    $sql .= " WHERE " . implode(" AND ", $conditions);
                }

                $sql .= " ORDER BY p.id ASC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
            } else {
                $stmt = $pdo->query($sql . " ORDER BY p.id ASC");
            }

            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($products as $row) {
            ?>
                <tr>
                    <th scope="row"><?= $row['id']; ?></th>
                    <td><?= $row['product_name']; ?></td>
                    <td><?= $row['cat_name']; ?></td>
                    <td><?= $row['price']; ?></td>
                    <td><?= $row['quantity']; ?></td>
                    <td><?= $row['product_availability']; ?></td>
                    <td><?= $row['date']; ?></td>
                    <td>
                        <!-- Edit and Delete Buttons -->
                        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id'] ?>">Edit</button>
                        <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal<?= $row['id'] ?>">Delete</button>
                    </td>
                </tr>

                <!-- Edit Modal -->
                <div class="modal fade" id="editModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="crud.php" method="post">
                                <div class="modal-header">
                                    <h5 class="modal-title">Edit Product</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <div class="mb-3">
                                        <label for="productName" class="form-label">Product Name</label>
                                        <input type="text" class="form-control" name="product_name" value="<?= $row['product_name'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="category" class="form-label">Category</label>
                                        <select class="form-select" name="category" required>
                                            <?php
                                            foreach ($categories as $cat) {
                                                $selected = ($cat['cat_id'] == $row['category']) ? 'selected' : '';
                                                echo "<option value='{$cat['cat_id']}' $selected>{$cat['cat_name']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="price" class="form-label">Price</label>
                                        <input type="number" class="form-control" name="price" value="<?= $row['price'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity</label>
                                        <input type="number" class="form-control" name="quantity" value="<?= $row['quantity'] ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="availability" class="form-label">Availability</label>
                                        <select class="form-select" name="availability" required>
                                            <option value="In Stock" <?= $row['product_availability'] == 'In Stock' ? 'selected' : '' ?>>In Stock</option>
                                            <option value="Out of Stock" <?= $row['product_availability'] == 'Out of Stock' ? 'selected' : '' ?>>Out of Stock</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label for="date" class="form-label">Date</label>
                                        <input type="date" class="form-control" name="date" value="<?= $row['date'] ?>" required>
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="update_product" class="btn btn-primary">Save changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Delete Modal -->
                <div class="modal fade" id="deleteModal<?= $row['id'] ?>" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="crud.php" method="post">
                                <div class="modal-header">
                                    <h5 class="modal-title">Delete Product</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <input type="hidden" name="product_id" value="<?= $row['id'] ?>">
                                    <p>Are you sure you want to delete this product?</p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" name="delete_product" class="btn btn-danger">Delete</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            <?php } ?>
        </tbody>
    </table>
</div>

<!-- Add Product Modal -->
<div class="modal fade" id="addModal" tabindex="-1" aria-labelledby="addModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="crud.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Add Product</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Add Product Form -->
                    <div class="mb-3">
                        <label for="productName" class="form-label">Product Name</label>
                        <input type="text" class="form-control" name="product_name" required>
                    </div>
                    <div class="mb-3">
                        <label for="category" class="form-label">Category</label>
                        <select class="form-select" name="category" required>
                            <?php
                            foreach ($categories as $cat) {
                                echo "<option value='{$cat['cat_id']}'>{$cat['cat_name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Price</label>
                        <input type="number" class="form-control" name="price" required>
                    </div>
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" name="quantity" required>
                    </div>
                    <div class="mb-3">
                        <label for="availability" class="form-label">Availability</label>
                        <select class="form-select" name="availability" required>
                            <option value="In Stock">In Stock</option>
                            <option value="Out of Stock">Out of Stock</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="date" class="form-label">Date</label>
                        <input type="date" class="form-control" name="date" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_product" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="categoryModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="crud.php" method="post">
                <div class="modal-header">
                    <h5 class="modal-title">Add Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <label for="cat_name" class="form-label">Category Name</label>
                    <input type="text" class="form-control" name="cat_name" required>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
