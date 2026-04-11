<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../includes/koneksi.php';

function franchisorHasBrand(mysqli $koneksi, int $userId): bool
{
    $brandStmt = $koneksi->prepare("SELECT brand_id FROM brands WHERE user_id = ? LIMIT 1");
    $brandStmt->bind_param("i", $userId);
    $brandStmt->execute();

    return (bool) $brandStmt->get_result()->fetch_assoc();
}

function getPostLoginRedirect(mysqli $koneksi, array $user): string
{
    $userId = (int) ($user['user_id'] ?? 0);
    $role = $user['role'] ?? 'user';

    if ($role === 'franchisor' && !franchisorHasBrand($koneksi, $userId)) {
        return 'register_brand.php';
    }

    return 'dashboard.php';
}

if (isset($_SESSION['user_id'])) {
    header("Location: " . getPostLoginRedirect($koneksi, [
        'user_id' => $_SESSION['user_id'],
        'role' => $_SESSION['user_role'] ?? 'user',
    ]));
    exit;
}

if(isset($_POST['login'])){
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $koneksi->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();

    if($data && password_verify($password, $data['password'])){
        $_SESSION['user_id'] = $data['user_id'];
        $_SESSION['user_name'] = $data['name'];
        $_SESSION['user_email'] = $data['email'];
        $_SESSION['user_role'] = $data['role'] ?? 'user';

        header("Location: " . getPostLoginRedirect($koneksi, $data));
        exit;
    } else {
        $error = "Email atau password salah!";
    }
}
?>

<?php include 'partials/header.php'; ?>

<div class="max-w-md mx-auto mt-20 card">
    <h2 class="text-xl font-bold mb-4">Login</h2>

    <?php if(isset($error)): ?>
        <div class="mb-3 rounded bg-red-100 p-2 text-red-700"><?= $error ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-3">
        <input type="email" name="email" placeholder="Email" class="input !mt-0" required>
        <input type="password" name="password" placeholder="Password" class="input !mt-0" required>
        <button name="login" class="btn w-full">Login</button>
    </form>
</div>

<?php include 'partials/footer.php'; ?>
