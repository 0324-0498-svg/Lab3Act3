<?php
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

require_once 'classes/Database.php';
require_once 'classes/MLModel.php';

$database = new Database();
$db = $database->getConnection();
 
$ml = new MLModel($db);
 
$history = $ml->preprocessData();
$predictedAmount = $ml->predictSales($history);
$topBuyers = $ml->getTopBuyers();
$clusters = $ml->getCustomerClusters();
 
$labels = [];
$values = [];
foreach($history as $index => $row) {
    $labels[] = "Sale " . ($index + 1);
    $values[] = $row['amount'];
}
$labels[] = "Predicted Next";
$values[] = $predictedAmount;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ML Dashboard - ORDERFLOW</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root { --sidebar-bg: #0d1b2a; --accent-blue: #3a86ff; }
        body { background-color: #f4f7f6; font-family: 'Segoe UI', sans-serif; }
        .sidebar { width: 260px; background-color: var(--sidebar-bg); color: white; position: fixed; height: 100%; }
        .nav-link { padding: 12px 25px; color: #adb5bd !important; text-decoration: none; display: block; }
        .nav-link.active { background-color: #1b263b; color: white !important; border-left: 4px solid var(--accent-blue); }
        .main-content { margin-left: 260px; padding: 40px; }
        .ai-card { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); background: white; height: 100%; }
        .explanation-section { background: white; border-radius: 15px; padding: 30px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .theory-box { background: #f8f9fa; border-left: 5px solid var(--accent-blue); padding: 20px; border-radius: 8px; height: 100%; }
    </style>
</head>
<body>

<div class="d-flex">
    <nav class="sidebar">
        <div class="p-4 text-center fw-bold text-uppercase border-bottom border-secondary">ORDERFLOW</div>
        <div class="nav flex-column mt-3">
            <a href="dashboard.php" class="nav-link"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
            <a href="analytics.php" class="nav-link active"><i class="bi bi-cpu-fill me-2"></i> ML Analytics</a>
            <a href="logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
        </div>
    </nav>

    <main class="main-content w-100">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold">ML Insights Dashboard</h2>
                <p class="text-muted">Automated customer clustering and revenue forecasting.</p>
            </div>
            <span class="badge bg-primary p-2 px-3 mb-3"><i class="bi bi-robot me-2"></i> AI-Powered System</span>
        </div>

        <div class="row g-4 mt-2 mb-5">
         
            <div class="col-md-8">
                <div class="card ai-card p-4">
                    <h5 class="fw-bold mb-3">Revenue Forecast Trend</h5>
                    <canvas id="salesChart" height="120"></canvas>
                </div>
            </div>
 
            <div class="col-md-4">
                <div class="card ai-card p-4 bg-primary text-white d-flex flex-column justify-content-center">
                    <h6 class="text-uppercase opacity-75 fw-bold small">Predicted Next Sale</h6>
                    <div class="display-5 fw-bold my-3">₱<?php echo number_format($predictedAmount, 2); ?></div>
                    <div>
                        <span class="badge bg-white text-primary rounded-pill px-3 py-2">Algorithm: Least Squares</span>
                    </div>
                </div>
            </div>
 
            <div class="col-md-6">
                <div class="card ai-card p-4">
                    <h6 class="fw-bold text-primary"><i class="bi bi-star-fill me-2"></i>Top 3 Frequent Buyers (ML Analysis)</h6>
                    <p class="small text-muted">Identified through frequency analysis of total successful transactions.</p>
                    <ul class="list-group list-group-flush mt-2">
                        <?php foreach($topBuyers as $customer): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo $customer['name']; ?>
                                <span class="badge bg-success rounded-pill"><?php echo $customer['total_orders']; ?> Orders</span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
 
            <div class="col-md-6">
                <div class="card ai-card p-4">
                    <h6 class="fw-bold text-primary"><i class="bi bi-layers-half me-2"></i>Customer Cluster Groups</h6>
                    <p class="small text-muted">Grouping customers based on their engagement levels.</p>
                    <div class="table-responsive mt-2">
                        <table class="table table-sm small">
                            <thead><tr><th>Customer Name</th><th>Target Cluster</th></tr></thead>
                            <tbody>
                                <?php foreach($clusters as $c): ?>
                                    <tr>
                                        <td><?php echo $c['name']; ?></td>
                                        <td>
                                            <span class="badge <?php echo ($c['cluster_group'] == 'High Value') ? 'bg-danger' : (($c['cluster_group'] == 'Medium Value') ? 'bg-warning text-dark' : 'bg-secondary'); ?>">
                                                <?php echo $c['cluster_group']; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
 
        <div class="explanation-section mb-5">
            <h4 class="fw-bold mb-4"><i class="bi bi-info-circle-fill text-primary me-2"></i>Detailed Explanation of Results</h4>
            
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="theory-box">
                        <h6 class="fw-bold text-primary">1. Revenue Forecasting (Regression Analysis)</h6>
                        <p class="small text-muted">
                            Our system utilizes the <strong>Least Squares Regression</strong> algorithm provided by the PHP-ML library. By mapping historical transaction amounts against their sequence (time-series), the model calculates a "line of best fit." 
                        </p>
                        <p class="small text-muted">
                            The predicted value of <strong>₱<?php echo number_format($predictedAmount, 2); ?></strong> represents the mathematical expectation for the next sale. This is crucial for <em>Business Intelligence</em>, as it allows the owner to forecast liquid cash flow and plan inventory restocks before the actual demand occurs.
                        </p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="theory-box">
                        <h6 class="fw-bold text-primary">2. Customer Clustering (Segmentation Logic)</h6>
                        <p class="small text-muted">
                            Instead of treating all users equally, the <strong>MLModel Class</strong> implements a clustering logic that categorizes residents or customers into three distinct tiers: <strong>High, Medium, and Low Value</strong>.
                        </p>
                        <p class="small text-muted">
                            This automated segmentation enables <em>Targeted Marketing</em>. For example, "High Value" clusters (customers with 5+ orders) can be targeted for loyalty rewards, while "Low/New" clusters can be sent promotional vouchers to encourage their second purchase, effectively increasing the system's overall retention rate.
                        </p>
                    </div>
                </div>
                <div class="col-12">
                    <div class="p-3 bg-light rounded border">
                        <h6 class="fw-bold"><i class="bi bi-code-slash me-2"></i>System Integration & OOP Methodology</h6>
                        <p class="small text-muted mb-0">
                            The implementation follows strict <strong>Object-Oriented Programming (OOP)</strong> principles. The <code>MLModel.php</code> class centralizes the data preprocessing, model training, and prediction logic. By separating the analytics engine from the UI (analytics.php), the system remains scalable—allowing for higer data accuracy and professional-grade code structure.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode($labels); ?>,
        datasets: [{
            label: 'Amount (₱)',
            data: <?php echo json_encode($values); ?>,
            borderColor: '#3a86ff',
            borderWidth: 3,
            tension: 0.3,
            fill: true,
            backgroundColor: 'rgba(58, 134, 255, 0.1)',
            pointRadius: 5,
            pointBackgroundColor: (context) => context.index === <?php echo count($values)-1; ?> ? '#ff006e' : '#3a86ff'
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true, ticks: { callback: (v) => '₱' + v } } }
    }
});
</script>
</body>
</html>
