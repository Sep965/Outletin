<?php
include '../includes/auth_admin.php';
$conn = new mysqli("localhost", "root", "", "outletin");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard Super Admin</title>

    <style>
        body {
            margin: 0;
            font-family: Arial;
            background: #f4f4f4;
        }

        /* SIDEBAR */
        .sidebar {
            width: 220px;
            height: 100vh;
            background: #800000;
            position: fixed;
            color: white;
            padding-top: 20px;
        }

        .sidebar h2 {
            text-align: center;
        }

        .sidebar a {
            display: block;
            padding: 12px;
            color: white;
            text-decoration: none;
        }

        .sidebar a:hover {
            background: #a52a2a;
        }

        /* MAIN */
        .main {
            margin-left: 220px;
            padding: 20px;
        }

        /* TITLE */
        .title {
            background: #800000;
            color: white;
            padding: 15px;
            border-radius: 10px;
        }

        /* TABLE */
        table {
            width: 100%;
            margin-top: 20px;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }

        th {
            background: #800000;
            color: white;
            padding: 10px;
        }

        td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f9f9f9;
        }

        /* BUTTON */
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin: 2px;
        }

        .approve { background: green; color: white; }
        .verified { background: blue; color: white; }
        .reject { background: red; color: white; }

        /* STATUS */
        .badge {
            padding: 5px 10px;
            border-radius: 5px;
            color: white;
        }

        .pending { background: orange; }
        .approved { background: green; }
        .verified-badge { background: blue; }
        .rejected { background: red; }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>SUPER ADMIN</h2>
    <a href="#">🏠 Dashboard</a>
    <a href="#">📩 Verifikasi Brand</a>
</div>

<!-- MAIN -->
<div class="main">

    <div class="title">
        <h2>Manajemen Brand</h2>
    </div>

    <table>
        <tr>
            <th>Brand</th>
            <th>Email</th>
            <th>Deskripsi</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>

<?php
$query = "
SELECT 
    b.brand_id,
    b.brand_name,
    b.description,
    u.email,
    v.status
FROM brands b
JOIN users u ON b.franchisor_id = u.user_id
LEFT JOIN verifications v ON b.brand_id = v.brand_id
";

$data = $conn->query($query);

while($row = $data->fetch_assoc()) {
    $status = $row['status'] ?? 'pending';
?>
        <tr>
            <td><?php echo $row['brand_name']; ?></td>
            <td><?php echo $row['email']; ?></td>
            <td><?php echo $row['description']; ?></td>

            <td>
                <span class="badge 
                    <?php 
                        if($status=='approved') echo 'approved';
                        elseif($status=='verified') echo 'verified-badge';
                        elseif($status=='rejected') echo 'rejected';
                        else echo 'pending';
                    ?>">
                    <?php echo strtoupper($status); ?>
                </span>
            </td>

            <td>
                <!-- APPROVED -->
                <form method="POST" action="proses_verifikasi.php" style="display:inline;">
                    <input type="hidden" name="brand_id" value="<?php echo $row['brand_id']; ?>">
                    <input type="hidden" name="action" value="approved">
                    <button class="btn approve">✔ Approved</button>
                </form>

                <!-- VERIFIED -->
                <form method="POST" action="proses_verifikasi.php" style="display:inline;">
                    <input type="hidden" name="brand_id" value="<?php echo $row['brand_id']; ?>">
                    <input type="hidden" name="action" value="verified">
                    <button class="btn verified">✔ Verified</button>
                </form>

                <!-- REJECT -->
                <form method="POST" action="proses_verifikasi.php" style="display:inline;">
                    <input type="hidden" name="brand_id" value="<?php echo $row['brand_id']; ?>">
                    <input type="hidden" name="action" value="rejected">
                    <button class="btn reject">✖ Reject</button>
                </form>
            </td>
        </tr>
<?php } ?>

    </table>

</div>

</body>
</html>