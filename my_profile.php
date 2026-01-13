<?php
session_start();
if (!isset($_SESSION['customer_id'])) {
    header("Location: customer_login.php");
    exit();
}

include 'head.php';
include 'config/database.php';
include 'customer_nav.php';

$error = '';
$success = '';

// Get customer data from users and customers tables using JOIN
$customer_id = $_SESSION['customer_id'];
$query = "SELECT u.*, c.phone 
          FROM users u 
          LEFT JOIN customers c ON u.id = c.id 
          WHERE u.id = '$customer_id' AND u.role = 'customer'";
$result = mysqli_query($conn, $query);
$customer = mysqli_fetch_assoc($result);

if (!$customer) {
    header("Location: customer_login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Basic validation
    if (empty($name) || empty($email)) {
        $error = "Name and email are required.";
    } else {
        // Check if email is taken by another user
        $email_check = "SELECT id FROM users WHERE email = '$email' AND id != '$customer_id'";
        $email_result = mysqli_query($conn, $email_check);
        
        if (mysqli_num_rows($email_result) > 0) {
            $error = "Email is already taken by another user.";
        } else {
            // Start transaction
            mysqli_begin_transaction($conn);
            try {
                // Update users table
                $update_user = "UPDATE users SET 
                              name = '$name',
                              email = '$email'
                              WHERE id = '$customer_id'";
                mysqli_query($conn, $update_user);

                // Update or insert into customers table
                $check_customer = "SELECT id FROM customers WHERE id = '$customer_id'";
                $customer_exists = mysqli_query($conn, $check_customer);
                
                if (mysqli_num_rows($customer_exists) > 0) {
                    $update_customer = "UPDATE customers SET 
                                      name = '$name',
                                      email = '$email',
                                      phone = '$phone'
                                      WHERE id = '$customer_id'";
                    mysqli_query($conn, $update_customer);
                } else {
                    $insert_customer = "INSERT INTO customers (id, name, email, phone) 
                                      VALUES ('$customer_id', '$name', '$email', '$phone')";
                    mysqli_query($conn, $insert_customer);
                }

                // Handle password change if requested
                if (!empty($current_password)) {
                    if (empty($new_password) || empty($confirm_password)) {
                        throw new Exception("Please fill in all password fields.");
                    }
                    
                    if ($new_password !== $confirm_password) {
                        throw new Exception("New passwords do not match.");
                    }
                    
                    // Verify current password
                    $hashed_current = hash('sha256', $current_password);
                    if ($hashed_current !== $customer['password']) {
                        throw new Exception("Current password is incorrect.");
                    }
                    
                    // Update password
                    $hashed_new = hash('sha256', $new_password);
                    $update_password = "UPDATE users SET password = '$hashed_new' WHERE id = '$customer_id'";
                    mysqli_query($conn, $update_password);
                }

                mysqli_commit($conn);
                
                // Update session variables
                $_SESSION['customer_name'] = $name;
                $_SESSION['customer_email'] = $email;
                
                $success = "Profile updated successfully!";
                
                // Refresh customer data
                $result = mysqli_query($conn, $query);
                $customer = mysqli_fetch_assoc($result);
                
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $error = $e->getMessage();
            }
        }
    }
}
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col">
                            <h4 class="mb-0">My Profile</h4>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($customer['name']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($customer['email']) ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
                            </div>
                        </div>

                        <hr class="my-4">
                        
                        <div class="row">
                            <div class="col-12">
                                <h5>Change Password</h5>
                                <p class="text-muted small">Leave blank if you don't want to change your password.</p>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control" id="current_password" name="current_password">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>

                            <div class="col-md-4 mb-3">
                                <label for="confirm_password" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-primary btn-lg w-100">Update Profile</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 