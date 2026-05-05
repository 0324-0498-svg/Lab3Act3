<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
$role = $_SESSION['role'] ?? 'staff';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Developers - ORDERFLOW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --sidebar-bg: #0d1b2a; --sidebar-hover: #1b263b; --accent-blue: #3a86ff; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .wrapper { display: flex; min-height: 100vh; }
        
        .sidebar { width: 260px; background-color: var(--sidebar-bg); color: white; position: fixed; height: 100%; z-index: 1000; }
        .sidebar-header { padding: 25px; font-size: 1.5rem; font-weight: bold; border-bottom: 1px solid rgba(255,255,255,0.1); text-align: center; letter-spacing: 2px; }
        .nav-link { padding: 12px 25px; color: #adb5bd !important; border-left: 4px solid transparent; text-decoration: none; display: block; transition: 0.3s; }
        .nav-link:hover, .nav-link.active { background-color: var(--sidebar-hover); color: white !important; border-left: 4px solid var(--accent-blue); }
        
        .main-content { flex: 1; margin-left: 260px; padding: 40px; }
        .dev-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); background: white; text-align: center; overflow: hidden; transition: 0.4s; height: 100%; }
        .dev-card:hover { transform: translateY(-10px); }
        .dev-img { height: 350px; width: 100%; object-fit: cover; border-bottom: 5px solid var(--accent-blue); }
    </style>
</head>
<body>

<div class="wrapper">
    <nav class="sidebar">
        <div class="sidebar-header text-uppercase">ORDERFLOW</div>
        <div class="nav flex-column mt-3">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="customers.php" class="nav-link"><i class="bi bi-people me-2"></i> Customers</a>
            <a href="orders.php" class="nav-link"><i class="bi bi-cart-check me-2"></i> Orders</a>
            <a href="reports.php" class="nav-link"><i class="bi bi-graph-up-arrow me-2"></i> Reports</a>
            
            <?php if ($role === 'admin'): ?>
                <div class="mt-2 mx-2 p-2" style="background: rgba(255,255,255,0.05); border-radius: 8px;">
                    <small class="ms-3 fw-bold text-uppercase" style="font-size: 0.75rem; color: #3a86ff; letter-spacing: 1px;">Admin Tools</small>
                    <a href="manage_users.php" class="nav-link border-0"><i class="bi bi-shield-lock-fill me-2"></i> Control Panel</a>
                </div>
            <?php endif; ?>

            <a href="developers.php" class="nav-link active"><i class="bi bi-code-slash me-2"></i> Developers</a>
            <hr class="mx-3 text-secondary">
            <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
        </div>
    </nav>

    <main class="main-content">
        <h2 class="fw-bold mb-5">Meet the Team</h2>

        <div class="row">
            <!-- Jhon Ivan Macalalad -->
            <div class="col-md-4 mb-4">
                <div class="dev-card">
                    <img src="https://scontent.fmnl13-1.fna.fbcdn.net/v/t39.30808-6/536273083_1886517788966959_4031959805840450181_n.jpg?stp=dst-jpg_p526x296_tt6&_nc_cat=100&ccb=1-7&_nc_sid=53a332&_nc_eui2=AeG_WrAZ2EmADB4aKRHDxUiul40eUVQn-j2XjR5RVCf6PTxdvftuvitagIEJGAFAW_QdbICeHCsWnDKT82NlQMVj&_nc_ohc=qSOpMoJm3coQ7kNvwEnJF_6&_nc_oc=AdqVcjP4iYGXmfrO2gIa5eCFQ0fQYlTGtxpQUKC1g5F0O7I8V4SSmUGP0bC26r7giOc&_nc_zt=23&_nc_ht=scontent.fmnl13-1.fna&_nc_gid=JuOrOXW9mleOOmqApbJxow&_nc_ss=7b2a8&oh=00_Af5_vxmZYjKeiNbTkBvYm9D458zrvE7bPffw5S9GmLQGCw&oe=69FE87E9" class="dev-img" referrerpolicy="no-referrer">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-1">Jhon Ivan Macalalad</h5>
                        <p class="text-primary small fw-bold mb-2">FRONT END DEVELOPER</p>
                        <i class="bi bi-code-slash text-primary fs-3"></i>
                    </div>
                </div>
            </div>

            <!-- Hyuan Bayani -->
            <div class="col-md-4 mb-4">
                <div class="dev-card">
                    <img src="https://scontent.fmnl13-1.fna.fbcdn.net/v/t39.30808-6/604846720_1183709600610999_8934256977768285803_n.jpg?_nc_cat=104&ccb=1-7&_nc_sid=53a332&_nc_eui2=AeHcnUB6S50hZZLV_SepUs34OWRFa5WeBUk5ZEVrlZ4FSQ2hYU-gBVlNLy8ZPjoCwkN9VYjU7bNskfhv-lVsiLUc&_nc_ohc=vP3SEGrxENAQ7kNvwEl6qdO&_nc_oc=AdoY7Nhjf858SezaXSd2RK3F4n6WY2gsY2tHUO1j2gNWwz-BTTm4VS-POp8cB-Y_qSw&_nc_zt=23&_nc_ht=scontent.fmnl13-1.fna&_nc_gid=uFfiEP8-wpdAvfOu7_-Ucw&_nc_ss=7b2a8&oh=00_Af5GHXfDa3APhrAsk_zhEI-xUxV2Qc819yH-nPHa0_dSFA&oe=69FE7E09" class="dev-img" referrerpolicy="no-referrer">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-1">Hyuan Bayani</h5>
                        <p class="text-primary small fw-bold mb-2">BACKEND DEVELOPER</p>
                        <i class="bi bi-database-fill text-primary fs-3"></i>
                    </div>
                </div>
            </div>

            <!-- Ayrand Gunda -->
            <div class="col-md-4 mb-4">
                <div class="dev-card">
                    <img src="https://scontent.fmnl13-1.fna.fbcdn.net/v/t1.15752-9/646361239_934333969179460_3552009092922974974_n.png?_nc_cat=104&ccb=1-7&_nc_sid=0024fc&_nc_eui2=AeG-1IfEQv8xLkt-xsr6WwjW4sxiyx4jZkDizGLLHiNmQCJOiFG8G9EXdJNB-U_atFqFSm0zlZDiRGOBi9Ivza0D&_nc_ohc=_tNN9AtFZ4cQ7kNvwFyzjDu&_nc_oc=AdohCS_vm1Ug02Fad74tROk2LwtM7smDjj1WEQw1CCk9r_6yq0-hAyrrxqGyKvrqldM&_nc_ad=z-m&_nc_cid=1066&_nc_zt=23&_nc_ht=scontent.fmnl13-1.fna&_nc_ss=7a22e&oh=03_Q7cD5QFx1McQvP1ACxcZPx9SppK_x3FMQFCcdNPMniZKxmRuEA&oe=6A2032FE" class="dev-img" referrerpolicy="no-referrer">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-1">Ayrand Gunda</h5>
                        <p class="text-primary small fw-bold mb-2">UI/UX DESIGNER</p>
                        <i class="bi bi-palette-fill text-primary fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

</body>
</html>
