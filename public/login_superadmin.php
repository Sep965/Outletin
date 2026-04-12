<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require __DIR__ . '/../includes/koneksi.php';

$error = '';
$email = '';

if (isset($_SESSION['admin_id']) && ($_SESSION['admin_role'] ?? '') === 'superadmin') {
    header("Location: Admin_Verification_Brand.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

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

            header("Location: Admin_Verification_Brand.php");
            exit;
        }

    } else {
        $error = "Email atau password salah!";
    }
}
?>
<?php include 'partials/header_auth.php'; ?>
<section class="min-h-screen px-5 py-8 sm:px-6 lg:flex lg:items-center lg:justify-center">
    <div class="grid w-full max-w-6xl gap-8 lg:grid-cols-[1.05fr_0.95fr] lg:items-stretch">
        <div class="relative hidden min-h-[620px] flex-col justify-between overflow-hidden rounded-[32px] bg-gradient-to-br from-[#5f0f1b] via-[#7a1625] to-[#9f2740] p-12 text-rose-50 shadow-[0_24px_80px_rgba(95,15,27,0.28)] lg:flex">
            <div class="pointer-events-none absolute -right-20 top-8 h-[260px] w-[260px] rounded-full bg-white/10"></div>
            <div class="pointer-events-none absolute -bottom-8 -left-10 h-[220px] w-[220px] rounded-full bg-rose-100/10"></div>

            <div class="relative z-10">
                <span class="inline-flex items-center rounded-full border border-white/20 bg-white/10 px-4 py-2 text-xs font-bold uppercase tracking-[0.22em]">
                    Outletin Admin
                </span>
                <h1 class="mt-6 max-w-[520px] text-4xl font-extrabold leading-tight xl:text-[46px]">
                    Panel superadmin yang lebih tegas, aman, dan nyaman dipakai.
                </h1>
                <p class="mt-5 max-w-[560px] text-base leading-8 text-rose-50/90">
                    Masuk untuk meninjau verifikasi brand, memantau aktivitas penting, dan menjaga kualitas ekosistem Outletin dari satu tempat.
                </p>
            </div>

            <div class="relative z-10 grid grid-cols-2 gap-4">
                <div class="rounded-[22px] border border-white/15 bg-white/10 p-5 backdrop-blur-sm">
                    <strong class="mb-2 block text-[15px]">Kontrol terpusat</strong>
                    <span class="text-sm leading-6 text-rose-50/85">Kelola persetujuan brand dan aktivitas admin dengan alur yang lebih jelas.</span>
                </div>
                <div class="rounded-[22px] border border-white/15 bg-white/10 p-5 backdrop-blur-sm">
                    <strong class="mb-2 block text-[15px]">Tema maroon</strong>
                    <span class="text-sm leading-6 text-rose-50/85">Tampilan dibuat lebih hidup agar area login terasa lebih premium dan konsisten.</span>
                </div>
            </div>
        </div>

        <div class="relative flex items-center">
            <div class="absolute left-12 right-12 top-9 z-0 h-36 rounded-full bg-red-600/20 blur-[70px]"></div>

            <div class="relative z-10 w-full overflow-hidden rounded-[32px] border border-red-950/10 bg-white/95 shadow-[0_18px_60px_rgba(95,15,27,0.16)]">
                <div class="bg-gradient-to-br from-[#7a1625] via-[#8f1d31] to-[#b0304a] px-6 py-8 text-white sm:px-10">
                    <div class="text-xs font-bold uppercase tracking-[0.24em] text-rose-100">Superadmin Login</div>
                    <h2 class="mt-3 text-3xl font-extrabold leading-tight sm:text-[34px]">Masuk ke dashboard superadmin</h2>
                    <p class="mt-3 text-sm leading-7 text-rose-50/90">
                        Gunakan akun superadmin untuk mengakses proses verifikasi dan pengelolaan data penting Outletin.
                    </p>
                </div>

                <div class="px-6 py-8 sm:px-10 sm:py-9">
                    <?php if ($error): ?>
                        <div class="mb-6 rounded-[18px] border border-red-200 bg-rose-50 px-4 py-3 text-sm font-bold text-red-700">
                            <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" novalidate class="space-y-5">
                        <div>
                            <label for="email" class="mb-2 block text-[15px] font-bold text-[#4a0d18]">Email Superadmin</label>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>"
                                placeholder="nama@outletin.com"
                                class="w-full rounded-[18px] border border-rose-200 bg-rose-50 px-4 py-3.5 text-[15px] text-slate-800 outline-none transition focus:border-[#8f1d31] focus:bg-white focus:ring-4 focus:ring-rose-200/60"
                                required
                            >
                        </div>

                        <div>
                            <div class="mb-2 flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between sm:gap-3">
                                <label for="password" class="text-[15px] font-bold text-[#4a0d18]">Password</label>
                                <span class="text-xs font-bold text-rose-700">Akses khusus administrator</span>
                            </div>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Masukkan password"
                                class="w-full rounded-[18px] border border-rose-200 bg-rose-50 px-4 py-3.5 text-[15px] text-slate-800 outline-none transition focus:border-[#8f1d31] focus:bg-white focus:ring-4 focus:ring-rose-200/60"
                                required
                            >
                        </div>

                        <button
                            type="submit"
                            class="w-full rounded-[18px] bg-gradient-to-br from-[#7a1625] via-[#8f1d31] to-[#b0304a] px-4 py-3.5 text-[15px] font-bold text-white shadow-[0_14px_30px_rgba(143,29,49,0.24)] transition hover:-translate-y-0.5 hover:brightness-105 hover:shadow-[0_16px_34px_rgba(143,29,49,0.28)]"
                        >
                            Login Superadmin
                        </button>
                    </form>

                    <div class="mt-5 rounded-[18px] border border-rose-200 bg-rose-50 px-4 py-3 text-sm leading-6 text-rose-900">
                        Hanya akun dengan role <strong>superadmin</strong> yang bisa masuk ke halaman ini.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'partials/footer_auth.php'; ?>
