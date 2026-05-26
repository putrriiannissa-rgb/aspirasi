<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family:Arial;
}

body{
    display:flex;
    background:#f5f5f5;
}

.sidebar{
    width:250px;
    min-height:100vh;
    background:#1e293b;
    color:white;
    padding:20px; position:fixed;
    left:0;
    top:0;
}

.sidebar h2{
    margin-bottom:30px;
}

.sidebar-menu{
    display:flex;
    flex-direction:column;
    gap:10px;
}

.sidebar-item{
    text-decoration:none;
    color:white;
    padding:12px;
    border-radius:8px;
    transition:0.3s;
}

.sidebar-item:hover{
    background:#334155;
}.logout{
    background:#dc2626;
}

.main-content{
    margin-left:250px;
    padding:30px;
    width:100%;
}

.card{
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.1);
}

table{
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

table th,
table td{
    border:1px solid #ddd;
    padding:10px;}
</style>
<div class="sidebar">
    <h2>ADMIN</h2>

    <div class="sidebar-menu">
        <a href="dashboard.php" class="sidebar-item">
            Dashboard
        </a>

        <a href="aspirasi.php" class="sidebar-item">
            Data Aspirasi
        </a>

        <a href="admin_data.php" class="sidebar-item">
            Kelola Admin
        </a>

        <a href="siswa.php" class="sidebar-item">
            Kelola Siswa
        </a>

        <a href="kategori.php" class="sidebar-item">
            Kelola Kategori
        </a>

        <a href="request.php" class="sidebar-item">
            Request Akun
        </a>

        <a href="../auth/logout.php" class="sidebar-item logout">
            Logout
        </a>
    </div>
</div>