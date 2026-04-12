<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../includes/koneksi.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $koneksi->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");

    if (!$stmt) {
        die("Query error: " . $koneksi->error);
    }

    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if ($data && password_verify($password, $data['password'])) {

        if ($data['role'] !== 'superadmin') {
            $error = "Bukan akun superadmin!";
        } else {

            session_regenerate_id(true);

            $_SESSION['admin_id'] = $data['user_id'];
            $_SESSION['admin_name'] = $data['name'];
            $_SESSION['admin_role'] = $data['role'];

            header("Location: admin_verification_brand.php");
            exit;
        }

    } else {
        $error = "Email atau password salah!";
    }
}
?>
<?php include 'partials/header_auth.php'; ?>

<div class="max-w-md mx-auto mt-20 card">

    <h2 class="text-xl font-bold mb-4 text-red-900">
        Login Superadmin
    </h2>

    <?php if($error): ?>
        <div class="mb-3 bg-red-100 text-red-700 p-2 rounded">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <form method="POST" class="space-y-4">

        <input type="email" name="email" placeholder="Email Superadmin" class="input" required>

        <input type="password" name="password" placeholder="Password" class="input" required>

        <button class="btn w-full">
            Login
        </button>

    </form>

</div>

<?php include 'partials/footer_auth.php'; ?>
