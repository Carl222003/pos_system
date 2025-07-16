<?php
require_once 'header.php';
?>
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4" style="color: #8B4543; font-size: 1.5rem; font-weight: 600; display: flex; align-items: center; gap: 0.75rem;">
        <i class="fas fa-balance-scale"></i>
        Branch Comparison
    </h1>
    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Compare Branches</h5>
                <p class="text-muted mb-0">Compare sales and performance metrics across branches</p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <select class="form-select form-select-sm" id="periodSelect" style="width: auto;">
                    <option value="daily">Today</option>
                    <option value="weekly">This Week</option>
                    <option value="monthly">This Month</option>
                    <option value="yearly">This Year</option>
                    <option value="custom">Custom</option>
                </select>
                <input type="date" id="startDate" class="form-control form-control-sm d-none" style="width: auto;">
                <input type="date" id="endDate" class="form-control form-control-sm d-none" style="width: auto;">
                <button class="btn btn-sm btn-primary" id="refreshComparison"><i class="fas fa-sync-alt"></i> Refresh</button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive mb-4">
                <table class="table table-hover" id="branchComparisonTable">
                    <thead>
                        <tr>
                            <th>Branch</th>
                            <th>Total Sales</th>
                            <th>Total Orders</th>
                            <th>Average Sale</th>
                            <th>Active Cashiers</th>
                            <th>Top Products</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be populated by JS -->
                    </tbody>
                </table>
            </div>
            <div>
                <canvas id="branchComparisonChart" height="300"></canvas>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
function formatCurrency(value) {
    return '₱' + parseFloat(value).toLocaleString('en-PH', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}
let branchComparisonChart = null;
function fetchBranchComparison() {
    const period = document.getElementById('periodSelect').value;
    let url = 'get_branch_comparison.php?period=' + period;
    if (period === 'custom') {
        const start = document.getElementById('startDate').value;
        const end = document.getElementById('endDate').value;
        if (start && end) {
            url += '&start_date=' + start + '&end_date=' + end;
        }
    }
    fetch(url)
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                populateComparisonTable(res.data);
                populateComparisonChart(res.data);
            }
        });
}
function populateComparisonTable(data) {
    const tbody = document.querySelector('#branchComparisonTable tbody');
    tbody.innerHTML = '';
    data.forEach(branch => {
        const topProducts = branch.top_products.map(p => `${p.product_name} (${p.total_quantity})`).join(', ');
        tbody.innerHTML += `
            <tr>
                <td>${branch.branch_name}</td>
                <td>${formatCurrency(branch.total_sales)}</td>
                <td>${branch.total_orders}</td>
                <td>${formatCurrency(branch.average_sale)}</td>
                <td>${branch.active_cashiers}</td>
                <td>${topProducts}</td>
            </tr>
        `;
    });
}
function populateComparisonChart(data) {
    const labels = data.map(b => b.branch_name);
    const sales = data.map(b => b.total_sales);
    if (branchComparisonChart) branchComparisonChart.destroy();
    const ctx = document.getElementById('branchComparisonChart').getContext('2d');
    branchComparisonChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Sales',
                data: sales,
                backgroundColor: '#8B4543'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return formatCurrency(context.raw);
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return formatCurrency(value);
                        }
                    }
                }
            }
        }
    });
}
document.getElementById('refreshComparison').addEventListener('click', fetchBranchComparison);
document.getElementById('periodSelect').addEventListener('change', function() {
    const period = this.value;
    document.getElementById('startDate').classList.toggle('d-none', period !== 'custom');
    document.getElementById('endDate').classList.toggle('d-none', period !== 'custom');
    fetchBranchComparison();
});
document.getElementById('startDate').addEventListener('change', fetchBranchComparison);
document.getElementById('endDate').addEventListener('change', fetchBranchComparison);
window.addEventListener('DOMContentLoaded', fetchBranchComparison);
</script>
<?php require_once 'footer.php'; ?> 