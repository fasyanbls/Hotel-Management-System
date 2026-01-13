<?php
session_start();

include 'head.php';
include 'config/database.php';

// Check if already logged in
if (isset($_SESSION['customer_id'])) {
    header("Location: index.php");
    exit();
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $error = "Please enter both email and password.";
    } else {
        // Sanitize email
        $email = mysqli_real_escape_string($conn, $email);
        
        // Hash the password for comparison
        $hashed_password = hash('sha256', $password);
        
        // Check if the email exists in users table
        $check_email = "SELECT u.*, c.phone FROM users u 
                       LEFT JOIN customers c ON u.id = c.id 
                       WHERE u.email = '$email' AND u.role = 'customer'";
        $email_result = mysqli_query($conn, $check_email);
        
        if (!$email_result) {
            $error = "Database error: " . mysqli_error($conn);
        } else if (mysqli_num_rows($email_result) == 0) {
            $error = "Email not found. Please register if you don't have an account.";
        } else {
            $user = mysqli_fetch_assoc($email_result);
            
            // Verify password
            if ($user['password'] === $hashed_password) {
                $_SESSION['customer_id'] = $user['id'];
                $_SESSION['customer_name'] = $user['name'];
                $_SESSION['customer_email'] = $user['email'];
                $_SESSION['customer_phone'] = $user['phone'];
                
                header("Location: index.php");
                exit();
            } else {
                $error = "Invalid password.";
            }
        }
    }
}
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Customer Login</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>" 
                                   required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">Login</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <p class="mb-0">Don't have an account? <a href="register.php" class="text-decoration-none">Register here</a></p>
                    <a href="index.php" class="text-decoration-none">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?> 