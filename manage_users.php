<?php
session_start();
include("includes/db.connection.php");

// Check if admin is logged in
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle Delete
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ? AND role='customer'");
    $stmt->bind_param("i", $delete_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_users.php");
    exit();
}

// Handle Edit
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ? AND role='customer'");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $edit_user = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

// Process Edit Form
if (isset($_POST['edit_user'])) {
    $edit_id = intval($_POST['id']);
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];
    $email = $_POST['email'];
    $role = 'customer'; // force role as customer

    $stmt = $conn->prepare("UPDATE users SET firstname=?, lastname=?, email=?, role=? WHERE id=?");
    $stmt->bind_param("ssssi", $firstname, $lastname, $email, $role, $edit_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_users.php");
    exit();
}

// Fetch all customers
$sql = "SELECT id, firstname, lastname, email, role FROM users WHERE role='customer' ORDER BY id DESC";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manage Users - Admin</title>
<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
<style>
body {
    background: linear-gradient(135deg, #fdfbfb, #ebedee);
    font-family: "Poppins", "Segoe UI", sans-serif;
    color: #333;
    min-height: 100vh;
    padding: 20px;
    display: flex;
    justify-content: center;
}

.container.layout_padding {
    padding: 40px 30px;
    background: rgba(255, 255, 255, 0.95);
    border-radius: 15px;
    box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
    max-width: 1000px;
    margin: auto;
}

h1.fashion_taital {
    text-align: center;
    color: #2c3e50;
    font-weight: 700;
    margin-bottom: 30px;
    font-size: 2.2rem;
}

/* Table */
table {
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
}
thead {
    background: linear-gradient(135deg, #2c3e50, #4b6584);
    color: white;
    text-transform: uppercase;
    font-size: 0.85rem;
}
tbody td {
    padding: 14px 12px;
    vertical-align: middle;
    border-bottom: 1px solid #eee;
    font-size: 0.95rem;
}
tbody tr:hover {
    background-color: #fdfdfd;
    transform: translateY(-1px);
    transition: all 0.2s ease-in-out;
}

/* Buttons - unified style */
.btn, .btn-lg, .btn-danger {
    font-size: 0.9rem;
    border-radius: 10px;
    padding: 10px 18px;
    font-weight: 600;
    text-decoration: none !important;
    color: #fff;
    border: none;
    background: linear-gradient(135deg, #4b7bec, #3867d6);
    transition: all 0.3s ease;
    display: inline-block;
}

.btn:hover, .btn-lg:hover, .btn-danger:hover {
    background: linear-gradient(135deg, #3867d6, #4b7bec);
    transform: translateY(-2px);
}

/* Specific color for "Delete" if needed */
.btn-danger {
    background: linear-gradient(135deg, #ff6b6b, #e63946);
}
.btn-danger:hover {
    background: linear-gradient(135deg, #e63946, #ff6b6b);
}

/* Responsive */
@media (max-width: 768px) {
    table, thead, tbody, th, td, tr {
        display: block;
    }
    thead tr {
        display: none;
    }
    tbody tr {
        margin-bottom: 15px;
        background: #fff;
        border-radius: 10px;
        padding: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    }
    tbody td {
        display: flex;
        justify-content: space-between;
        padding: 10px;
    }
    tbody td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #2c3e50;
    }
}

a {
    text-decoration: none !important;
}
</style>

</head>
<body>


<div class="container layout_padding">
    <a href="admin_dashboard.php" class="btn btn-lg mb-3">⬅ Back to Dashboard</a>
    <h1 class="fashion_taital">Manage Users</h1>

    <!-- Edit form -->
    <?php if($edit_user): ?>
    <div class="mb-4">
        <h3>Edit User ID <?= $edit_user['id'] ?></h3>
        <form method="POST">
            <input type="hidden" name="id" value="<?= $edit_user['id'] ?>">
            <div class="mb-2">
                <input type="text" name="firstname" value="<?= htmlspecialchars($edit_user['firstname']) ?>" placeholder="First Name" class="form-control" required>
            </div>
            <div class="mb-2">
                <input type="text" name="lastname" value="<?= htmlspecialchars($edit_user['lastname']) ?>" placeholder="Last Name" class="form-control" required>
            </div>
            <div class="mb-2">
                <input type="email" name="email" value="<?= htmlspecialchars($edit_user['email']) ?>" placeholder="Email" class="form-control" required>
            </div>
            <div class="mb-2">
                <select name="role" class="form-control" required>
                    <option value="user" <?= $edit_user['role'] === 'user' ? 'selected' : '' ?>>User</option>
                    <option value="admin" <?= $edit_user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                </select>
            </div>
            <button type="submit" name="edit_user" class="btn btn-primary">Update User</button>
            <a href="manage_users.php" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
    <?php endif; ?>

    <!-- Users table -->
    <?php if($result->num_rows > 0): ?>
    <table class="table table-striped table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= $row['id']; ?></td>
                <td><?= htmlspecialchars($row['firstname']); ?></td>
                <td><?= htmlspecialchars($row['lastname']); ?></td>
                <td><?= htmlspecialchars($row['email']); ?></td>
                <td><?= htmlspecialchars($row['role']); ?></td>
                <td>
                    <a href="?edit=<?= $row['id']; ?>" class="btn btn-edit btn-sm me-2">Edit</a>
                    <a href="?delete=<?php echo $product['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
        <div class="alert alert-warning text-center">
            No users found.
        </div>
    <?php endif; ?>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
</body>
</html>
