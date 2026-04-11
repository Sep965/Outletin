<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../includes/koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$error = '';
$success = '';
$name = '';
$email = '';
$role = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? '';
    $allowedRoles = ['franchisor', 'franchisee'];

    if ($name === '' || $email === '' || $password === '' || $role === '') {
        $error = "Semua field wajib diisi";
    } elseif (!in_array($role, $allowedRoles, true)) {
        $error = "Role tidak valid";
    } else {

        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $koneksi->prepare(
            "INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)"
        );
        $stmt->bind_param("ssss", $name, $email, $hash, $role);

        if ($stmt->execute()) {
            $success = "Berhasil daftar, silakan login";
        } else {
            $error = "Email sudah digunakan";
        }
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="/outletin/scr/output.tmp.css">
</head>

<body class="bg-red-50">

    <div class="card max-w-md mx-auto mt-24">

        <h2 class="text-center text-xl font-bold text-red-900 mb-4">Register</h2>

        <?php if ($error): ?>
            <div class="mb-3 rounded bg-red-100 p-2 text-red-700"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="mb-3 rounded bg-emerald-100 p-2 text-emerald-700"><?= $success ?></div>
        <?php endif; ?>

        <form method="post" class="space-y-3">

            <input type="text" name="name" placeholder="Nama" required class="input !mt-0"
                value="<?= htmlspecialchars($name, ENT_QUOTES) ?>">

            <input type="email" name="email" placeholder="Email" required class="input !mt-0"
                value="<?= htmlspecialchars($email, ENT_QUOTES) ?>">

            <input type="password" name="password" placeholder="Password" required class="input !mt-0">

            <select name="role" required class="input !mt-0">
                <option value="">Pilih role</option>
                <option value="franchisor" <?= $role === 'franchisor' ? 'selected' : '' ?>>Franchisor</option>
                <option value="franchisee" <?= $role === 'franchisee' ? 'selected' : '' ?>>Franchisee</option>
            </select>

            <button class="btn w-full">
                Daftar
            </button>

        </form>

        <p class="text-sm mt-3 text-center">
            Sudah punya akun?
            <a href="login.php" class="text-red-800">Login</a>
        </p>

    </div>

</body>

</html>