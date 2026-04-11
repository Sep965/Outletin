<?php
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/auth.php';

$user_id = $_SESSION['user_id'];
$error = "";

// ================= GET OUTLETS =================
$outlets = $koneksi->prepare("SELECT * FROM outlets WHERE user_id=?");
$outlets->bind_param("i", $user_id);
$outlets->execute();
$outletList = $outlets->get_result();

// ================= CREATE =================
if (isset($_POST['create'])) {

    $name = trim($_POST['product_name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $outlet_id = intval($_POST['outlet_id']);

    if ($name === '' || $price <= 0 || $stock < 0) {
        $error = "Data tidak valid";
    } else {

        $stmt = $koneksi->prepare(
            "INSERT INTO products (product_name, price, stock, outlet_id, user_id)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sdiii", $name, $price, $stock, $outlet_id, $user_id);
        $stmt->execute();
    }
}

// ================= UPDATE =================
if (isset($_POST['update'])) {

    $id = intval($_POST['id']);
    $name = trim($_POST['product_name']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $outlet_id = intval($_POST['outlet_id']);

    $stmt = $koneksi->prepare(
        "UPDATE products 
         SET product_name=?, price=?, stock=?, outlet_id=? 
         WHERE product_id=? AND user_id=?"
    );
    $stmt->bind_param("sdiiii", $name, $price, $stock, $outlet_id, $id, $user_id);
    $stmt->execute();
}

// ================= DELETE =================
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = $koneksi->prepare(
        "DELETE FROM products WHERE product_id=? AND user_id=?"
    );
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();

    header("Location: products.php");
    exit;
}

// ================= GET DATA =================
$data = $koneksi->prepare(
    "SELECT p.*, o.outlet_name 
     FROM products p
     LEFT JOIN outlets o ON p.outlet_id = o.outlet_id
     WHERE p.user_id=?
     ORDER BY p.product_id DESC"
);
$data->bind_param("i", $user_id);
$data->execute();
$result = $data->get_result();

include 'partials/header.php';
?>

<!-- HEADER -->
<div class="flex justify-between items-center mb-4">
  <h2 class="text-xl font-bold text-red-900">Manajemen Produk</h2>

  <button onclick="openModal()" class="btn">
    + Tambah Produk
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
<th class="p-3">Nama</th>
<th class="p-3">Harga</th>
<th class="p-3">Stok</th>
<th class="p-3">Outlet</th>
<th class="p-3">Aksi</th>
</tr>
</thead>

<tbody>

<?php while($row = $result->fetch_assoc()): ?>

<tr class="border-t text-center">

<td class="p-3"><?= htmlspecialchars($row['product_name']) ?></td>
<td class="p-3">Rp <?= number_format($row['price']) ?></td>
<td class="p-3"><?= $row['stock'] ?></td>
<td class="p-3"><?= htmlspecialchars($row['outlet_name']) ?></td>

<td class="p-3 space-x-2">

<button onclick="editData(
<?= $row['product_id'] ?>,
'<?= htmlspecialchars($row['product_name'], ENT_QUOTES) ?>',
<?= $row['price'] ?>,
<?= $row['stock'] ?>,
<?= $row['outlet_id'] ?>
)"
class="bg-yellow-400 px-3 py-1 rounded">
Edit
</button>

<a href="products.php?delete=<?= $row['product_id'] ?>"
onclick="return confirm('Yakin hapus?')"
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
Tambah Produk
</h3>

<form method="post" class="space-y-3">

<input type="hidden" name="id" id="id">

<input type="text" name="product_name" id="name"
placeholder="Nama Produk"
class="input !mt-0">

<input type="number" name="price" id="price"
placeholder="Harga"
class="input !mt-0">

<input type="number" name="stock" id="stock"
placeholder="Stok"
class="input !mt-0">

<select name="outlet_id" id="outlet"
class="input !mt-0">

<option value="">-- Pilih Outlet --</option>

<?php 
$outlets->execute();
$outletList = $outlets->get_result();
while($o = $outletList->fetch_assoc()):
?>
<option value="<?= $o['outlet_id'] ?>"><?= $o['outlet_name'] ?></option>
<?php endwhile; ?>

</select>

<button id="submitBtn"
name="create"
class="btn w-full">
Simpan
</button>

</form>

<button onclick="closeModal()"
class="mt-3 w-full rounded-lg border border-red-300 py-2 text-red-800">
Tutup
</button>

</div>
</div>

<!-- SCRIPT -->
<script>
function openModal(){
  const modal = document.getElementById('modal');
  modal.classList.remove('hidden');
  modal.classList.add('flex');
}

function closeModal(){
  const modal = document.getElementById('modal');
  modal.classList.add('hidden');
  modal.classList.remove('flex');
}

function editData(id, name, price, stock, outlet_id){

  openModal();

  document.getElementById('modalTitle').innerText = "Edit Produk";
  document.getElementById('submitBtn').innerText = "Update";

  document.getElementById('id').value = id;
  document.getElementById('name').value = name;
  document.getElementById('price').value = price;
  document.getElementById('stock').value = stock;
  document.getElementById('outlet').value = outlet_id;

  document.getElementById('submitBtn').name = "update";
}
</script>

<?php include 'partials/footer.php'; ?>
