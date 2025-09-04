<?php
session_start();

require_once '../config.php';
require_once '../includes/db.php';

$error_message = '';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $error_message = 'Per favore, inserisci sia username che password.';
    } else {
        $stmt = $conn->prepare("SELECT username, password_hash FROM admin_users WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_username'] = $user['username'];
                header('Location: dashboard.php');
                exit();
            } else {
                $error_message = 'Credenziali non valide.';
            }
        } else {
            $error_message = 'Credenziali non valide.';
        }
        $stmt->close();
    }
}
$conn->close();

ob_start();
?>

<div class="card login-card">
    <div class="login-header text-center">
        <h3 class="mb-0">Admin Login</h3>
        <p class="mb-0"><small>Social Gioco Tris</small></p>
    </div>
    <div class="card-body p-4">
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><i class="bi bi-exclamation-triangle-fill me-2"></i><?php echo $error_message; ?></div>
        <?php endif; ?>
        <form action="login.php" method="POST" novalidate>
            <div class="mb-3">
                <label for="username" class="form-label">Username</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                    <input type="text" class="form-control" id="username" name="username" required>
                </div>
            </div>
            <div class="mb-4">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">Accedi</button>
            </div>
        </form>
    </div>
</div>

<?php
$content = ob_get_clean();
require 'partials/login-layout.php';
?>
