<?php
session_start(); 

include 'head.php';
include 'config/database.php';

// Check if already logged in
if (isset($_SESSION['admin'])) {
    header("Location: dashboard.php");
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
        
        // Check if the email exists and user is admin
        $query = "SELECT * FROM users WHERE email = '$email' AND role = 'admin'";
        $result = mysqli_query($conn, $query);
        
        if (!$result) {
            $error = "Database error: " . mysqli_error($conn);
        } else if (mysqli_num_rows($result) == 0) {
            $error = "Invalid email or password.";
        } else {
            $user = mysqli_fetch_assoc($result);
            
            // For the default admin account
            if ($email === 'admin@hotel.com' && $password === 'admin') {
                $_SESSION['admin'] = true;
                $_SESSION['admin_id'] = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                
                header("Location: dashboard.php");
                exit();
            } else {
                // For any other admin accounts, use normal password verification
                $hashed_password = hash('sha256', $password);
                if ($user['password'] === $hashed_password) {
                    $_SESSION['admin'] = true;
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['admin_name'] = $user['name'];
                    
                    header("Location: dashboard.php");
                    exit();
                } else {
                    $error = "Invalid email or password.";
                }
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
                    <h4 class="mb-0">Admin Login</h4>
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
                    <a href="index.php" class="text-decoration-none">Back to Home</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
