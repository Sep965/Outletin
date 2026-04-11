<?php
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/auth.php';

$user_id = $_SESSION['user_id'];
$error = "";

if (isset($_POST['create'])) {

    $name = trim($_POST['outlet_name']);
    $location = trim($_POST['location']);

    if ($name === '' || $location === '') {
        $error = "Semua field wajib diisi";
    } else {
        $stmt = $koneksi->prepare(
            "INSERT INTO outlets (outlet_name, location, user_id) VALUES (?, ?, ?)"
        );
        $stmt->bind_param("ssi", $name, $location, $user_id);
        $stmt->execute();
    }
}

if (isset($_POST['update'])) {

    $id = intval($_POST['id']);
    $name = trim($_POST['outlet_name']);
    $location = trim($_POST['location']);

    $stmt = $koneksi->prepare(
        "UPDATE outlets SET outlet_name=?, location=? WHERE outlet_id=? AND user_id=?"
    );
    $stmt->bind_param("ssii", $name, $location, $id, $user_id);
    $stmt->execute();
}

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = $koneksi->prepare(
        "DELETE FROM outlets WHERE outlet_id=? AND user_id=?"
    );
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();

    header("Location: outlet.php");
    exit;
}

$data = $koneksi->prepare(
    "SELECT * FROM outlets WHERE user_id=? ORDER BY outlet_id DESC"
);
$data->bind_param("i", $user_id);
$data->execute();
$result = $data->get_result();

include 'partials/header.php';
?>

<div class="flex justify-between items-center mb-4">
    <h2 class="text-xl font-bold text-red-900">Manajemen Outlet</h2>

    <button onclick="openModal()" class="btn">
        + Tambah Outlet
    </button>
</div>

<?php if ($error): ?>
    <div class="mb-3 rounded bg-red-100 p-2 text-red-700"><?= $error ?></div>
<?php endif; ?>

<!-- TABLE -->
<div class="overflow-hidden rounded-xl border border-red-100 bg-white shadow">

    <table class="w-full">

        <thead class="bg-red-800 text-white">
            <tr>
                <th class="p-3 text-left">Nama</th>
                <th class="p-3 text-left">Lokasi</th>
                <th class="p-3">Aksi</th>
            </tr>
        </thead>

        <tbody>

            <?php while ($row = $result->fetch_assoc()): ?>

                <tr class="border-t">

                    <td class="p-3"><?= htmlspecialchars($row['outlet_name']) ?></td>
                    <td class="p-3"><?= htmlspecialchars($row['location']) ?></td>

                    <td class="p-3 text-center space-x-2">

                        <button
                            onclick="editData(<?= $row['outlet_id'] ?>, '<?= htmlspecialchars($row['outlet_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($row['location'], ENT_QUOTES) ?>')"
                            class="bg-yellow-400 px-3 py-1 rounded">
                            Edit
                        </button>

                        <a href="outlet.php?delete=<?= $row['outlet_id'] ?>" onclick="return confirm('Yakin hapus?')"
                            class="bg-red-600 text-white px-3 py-1 rounded">
                            Hapus
                        </a>

                    </td>

                </tr>

            <?php endwhile; ?>

        </tbody>
    </table>

</div>

<!-- MODAL -->
<div id="modal" class="fixed inset-0 hidden items-center justify-center bg-black/50">

    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow">

        <h3 class="text-lg font-bold text-red-900 mb-4" id="modalTitle">
            Tambah Outlet
        </h3>

        <form method="post" class="space-y-3">

            <input type="hidden" name="id" id="id">

            <input type="text" name="outlet_name" id="name" placeholder="Nama Outlet" class="input !mt-0">

            <input type="text" name="location" id="location" placeholder="Lokasi" class="input !mt-0">

            <button id="submitBtn" name="create" class="btn w-full">
                Simpan
            </button>

        </form>

        <button onclick="closeModal()" class="mt-3 w-full rounded-lg border border-red-300 py-2 text-red-800">
            Tutup
        </button>

    </div>
</div>

<!-- SCRIPT -->
<script>
    function openModal() {
        const modal = document.getElementById('modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeModal() {
        const modal = document.getElementById('modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    // EDIT MODE
    function editData(id, name, location) {

        openModal();

        document.getElementById('modalTitle').innerText = "Edit Outlet";
        document.getElementById('submitBtn').innerText = "Update";

        document.getElementById('id').value = id;
        document.getElementById('name').value = name;
        document.getElementById('location').value = location;

        document.getElementById('submitBtn').name = "update";
    }
</script>

<?php include 'partials/footer.php'; ?>