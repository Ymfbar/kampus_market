<?php
include 'includes/config.php';

$q = isset($_GET['q']) ? trim($_GET['q']) : '';
$kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;

// Kalau query kurang dari 1 huruf, return array kosong
if(strlen($q) < 1){
    echo json_encode([]);
    exit;
}

// Base query
$sql = "
    SELECT i.id, i.nama_barang, i.harga, i.foto, c.nama_kategori AS kategori
    FROM items i
    LEFT JOIN categories c ON i.kategori_id = c.id
    WHERE i.nama_barang LIKE ?
";
$params = ["%$q%"];
$types = "s";

// Tambah filter kategori kalau dipilih
if($kategori > 0){
    $sql .= " AND i.kategori_id = ?";
    $params[] = $kategori;
    $types .= "i";
}

// Urutkan dan batasi hasil
$sql .= " ORDER BY i.created_at DESC LIMIT 10";

$stmt = $conn->prepare($sql);

// Bind parameter secara dinamis
if(count($params) > 1){
    $stmt->bind_param($types, ...$params);
}else{
    $stmt->bind_param($types, $params[0]);
}

$stmt->execute();
$res = $stmt->get_result();

$items = [];
while($row = $res->fetch_assoc()){
    $items[] = [
        'id' => $row['id'],
        'nama_barang' => $row['nama_barang'],
        'harga' => number_format($row['harga']),
        'foto' => $row['foto'] ? 'uploads/'.$row['foto'] : 'assets/img/placeholder.png',
        'kategori' => $row['kategori'] ?? 'Uncategorized'
    ];
}

// Kembalikan JSON
header('Content-Type: application/json');
echo json_encode($items);
