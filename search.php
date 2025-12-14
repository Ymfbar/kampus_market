<?php include 'includes/header.php'; ?>

<?php
$categories = $conn->query("SELECT * FROM categories ORDER BY nama_kategori ASC");

$defaultItems = $conn->query("
    SELECT i.*, c.nama_kategori AS kategori
    FROM items i
    LEFT JOIN categories c ON i.kategori_id = c.id
    WHERE i.status = 'approved' /* FILTER DITAMBAHKAN */
    ORDER BY i.created_at DESC
    LIMIT 12
");

$defaultArr = [];
while($row = $defaultItems->fetch_assoc()){
    $row['foto'] = $row['foto'] ? 'uploads/'.$row['foto'] : 'assets/img/placeholder.png';
    $row['harga'] = number_format($row['harga']);
    $defaultArr[] = $row;
}
?>

<div class="container my-5 d-flex flex-column align-items-center">

    <!-- FOTO/LOGO DI TENGAH -->
    <div class="mb-2 text-center">
        <img src="uploads/logoku.png" 
             alt="Logo"
             style="width:1200px; height:120px; object-fit:contain;">
    </div>

    <!-- SEARCH CARD -->
    <div class="col-lg-8 col-md-10 w-100">
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="p-4">

                <div class="row g-2 mb-3 align-items-center">

                    <!-- INPUT SEARCH -->
                    <div class="col-md-6 position-relative">
                        <input id="searchInput"
                               class="form-control rounded-3"
                               placeholder="Cari nama barang">
                        <div id="suggestions"
                             class="list-group position-absolute w-100 mt-1 shadow-sm"
                             style="z-index:1000;"></div>
                    </div>

                    <!-- KATEGORI SELECT -->
                    <div class="col-md-4">
                        <select id="kategoriSelect" class="form-select rounded-3">
                            <option value="">Semua Kategori</option>
                            <?php while($cat = $categories->fetch_assoc()): ?>
                                <option value="<?= $cat['id'] ?>">
                                    <?= htmlspecialchars($cat['nama_kategori']) ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- BUTTON SEARCH -->
                    <div class="col-md-2 d-grid">
                        <button id="searchBtn" class="btn btn-dark rounded-3">
                            Cari
                        </button>
                    </div>

                </div>

                <!-- RESULT -->
                <div id="searchResults" class="row g-4 mt-4"></div>

            </div>
        </div>
    </div>

</div>

<style>
/* ITEM CARD */
.item-card {
    transition: .2s ease;
    border-radius: 16px;
}
.item-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 30px rgba(0,0,0,.08);
}
.item-card img {
    border-top-left-radius: 16px;
    border-top-right-radius: 16px;
}
.price {
    font-weight: 600;
    color: #111;
}
.badge-category {
    background: #f1f1f1;
    color: #555;
    font-weight: 500;
}
</style>

<script>
const input = document.getElementById('searchInput');
const kategori = document.getElementById('kategoriSelect');
const suggestions = document.getElementById('suggestions');
const results = document.getElementById('searchResults');
const searchBtn = document.getElementById('searchBtn');

const defaultItems = <?= json_encode($defaultArr) ?>;

function renderItems(items){
    results.innerHTML = '';
    if(items.length === 0){
        results.innerHTML = `
            <div class="text-center text-muted mt-5">
                Barang tidak ditemukan
            </div>`;
        return;
    }

    items.forEach(item => {
        const col = document.createElement('div');
        col.className = 'col-md-3';
        col.innerHTML = `
        <div class="card border-0 shadow-sm h-100 item-card">
            <img src="${item.foto}"
                 class="card-img-top"
                 style="height:180px;object-fit:cover;">
            <div class="card-body d-flex flex-column">
                <h6 class="fw-semibold mb-1">${item.nama_barang}</h6>
                <div class="price mb-1">Rp ${item.harga}</div>
                <span class="badge badge-category mb-3">
                    ${item.kategori || 'Uncategorized'}
                </span>
                <a href="detail.php?id=${item.id}"
                   class="btn btn-outline-dark btn-sm mt-auto rounded-3">
                   Lihat Detail
                </a>
            </div>
        </div>`;
        results.appendChild(col);
    });
}

renderItems(defaultItems);

function fetchData(){
    const q = input.value.trim();
    const cat = kategori.value;

    if(!q){
        renderItems(defaultItems);
        suggestions.innerHTML = '';
        return;
    }

    fetch(`search_ajax.php?q=${encodeURIComponent(q)}&kategori=${cat}`)
        .then(res => res.json())
        .then(data => {
            suggestions.innerHTML = '';
            data.forEach(i => {
                const a = document.createElement('a');
                a.className = 'list-group-item list-group-item-action';
                a.textContent = i.nama_barang;
                a.href = `detail.php?id=${i.id}`;
                suggestions.appendChild(a);
            });
            renderItems(data);
        });
}

input.addEventListener('input', fetchData);
kategori.addEventListener('change', fetchData);
searchBtn.addEventListener('click', fetchData);
</script>

<?php include 'includes/footer.php'; ?>
