<?php
require_once 'db_connect.php';
require_once 'auth_function.php';

checkCashierLogin();

$confData = getConfigData($pdo);

include('header.php');
?>

<!-- Order History Dashboard Style -->
<div class="container-fluid px-4">
    <!-- Order History Card -->
    <div class="row justify-content-center">
        <div class="col-xl-12">
            <div class="card shadow-sm border-0 mb-4 order-history-card">
                <div class="card-header d-flex align-items-center justify-content-between order-history-header">
                    <div class="d-flex align-items-center gap-3">
                        <span class="d-flex align-items-center justify-content-center order-history-icon">
                            <i class="fas fa-history fa-2x"></i>
                        </span>
                        <div>
                            <h2 class="mb-0 order-history-title">Order History Table</h2>
                            <small class="text-muted">View and manage all past orders</small>
                        </div>
                    </div>
                </div>
                <!-- Professional filter/search row: gradient, shadow, effects, and modern controls -->
                <div class="card-body p-4">
                    <div class="filter-row d-flex flex-wrap align-items-center gap-2 mb-3 p-3" style="background: linear-gradient(90deg, #f8f9fa 60%, #f3e5f5 100%); box-shadow: 0 2px 12px rgba(140, 98, 57, 0.07); border: 1px solid #e0e0e0; border-radius: 0.75rem;">
                        <div id="orderHistoryTable_length" class="dataTables_length flex-shrink-0" style="min-width: 120px;"></div>
                        <input type="date" id="startDate" class="form-control form-control-sm filter-control" style="min-width: 130px; max-width: 150px; border-radius: 0.5rem; transition: box-shadow 0.2s, border-color 0.2s;">
                        <button id="filterBtn" class="btn btn-primary btn-sm filter-control" type="button" style="border-radius: 0.5rem; box-shadow: 0 2px 8px rgba(33,150,243,0.08); transition: box-shadow 0.2s, background 0.2s;"><i class="fas fa-filter"></i> Filter</button>
                        <div id="orderHistoryTable_filter" class="dataTables_filter flex-shrink-0 ms-auto" style="min-width: 220px;"></div>
                    </div>
                    <div class="table-responsive">
                        <table id="orderHistoryTable" class="table table-striped table-hover align-middle mb-0 order-history-table">
                            <thead class="table-light">
                                <tr>
                                    <th>DATE</th>
                                    <th>TIME</th>
                                    <th>ORDER NUMBER</th>
                                    <th>ITEMS</th>
                                    <th>TOTAL</th>
                                    <th>ACTION</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- DataTables will populate this -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Chart Placeholder (optional) -->
    <!-- <div class="row mb-4">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Order Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="orderTrendChart" height="300"></canvas>
                </div>
            </div>
        </div>
    </div> -->
</div>
<style>
body {
    background: #f8f9fa !important;
}
.main-stats-card {
    border-radius: 1rem;
    box-shadow: 0 2px 16px rgba(140, 98, 57, 0.07), 0 1.5px 4px rgba(0,0,0,0.03);
    background: #fff;
    padding: 1.5rem 1.25rem;
    margin-bottom: 0.5rem;
}
.order-history-card {
    border-radius: 1.25rem;
    box-shadow: 0 4px 24px rgba(140, 98, 57, 0.10), 0 2px 8px rgba(0,0,0,0.04);
    background: #fff;
    border-left: 6px solid #8B4543;
}
.order-history-header {
    border-radius: 1.25rem 1.25rem 0 0;
    background: linear-gradient(90deg, #f3e5f5 0%, #fff 100%);
    border-bottom: 1px solid #eee;
    padding-top: 1.5rem;
    padding-bottom: 1.5rem;
}
.order-history-icon {
    background: #8B4543;
    color: #fff;
    border-radius: 50%;
    width: 56px;
    height: 56px;
    box-shadow: 0 2px 8px rgba(140, 98, 57, 0.10);
}
.order-history-title {
    font-weight: 800;
    color: #8B4543;
    letter-spacing: 0.5px;
    font-size: 2rem;
}
.order-history-table {
    border-radius: 0.75rem;
    overflow: hidden;
    background: #fff;
}
.dataTables_length { display: none !important; }
#orderHistoryTable tbody tr:hover {
    background: #f3e5f5 !important;
    transition: background 0.2s;
}
#orderHistoryTable th, #orderHistoryTable td {
    vertical-align: middle;
    font-size: 1rem;
}
#orderHistoryTable th {
    font-weight: 700;
    color: #8B4543;
    background: #f8f9fa;
    border-top: none;
}
#orderHistoryTable td {
    background: #fff;
}
.filter-row .filter-control:focus {
    box-shadow: 0 0 0 2px #8B4543, 0 2px 8px rgba(140, 98, 57, 0.10);
    border-color: #8B4543;
    outline: none;
    background: #fffbe9;
    transition: box-shadow 0.2s, border-color 0.2s, background 0.2s;
}
#filterBtn.filter-control:hover, #filterBtn.filter-control:focus {
    background: linear-gradient(90deg, #8B4543 80%, #9C27B0 100%);
    box-shadow: 0 4px 16px rgba(140, 98, 57, 0.13);
    border-color: #8B4543;
    color: #fff;
    transition: box-shadow 0.2s, background 0.2s, border-color 0.2s;
}
</style>

<script>
$(document).ready(function() {
    // Set default date
    const today = new Date();
    const thirtyDaysAgo = new Date(today);
    thirtyDaysAgo.setDate(today.getDate() - 30);
    $('#startDate').val(thirtyDaysAgo.toISOString().split('T')[0]);

    // Initialize DataTable
    const table = $('#orderHistoryTable').DataTable({
        processing: true,
        serverSide: true,
        lengthMenu: [ [5, 10, 25, 50, 100, -1], [5, 10, 25, 50, 100, 'All'] ],
        pageLength: 10,
        ajax: {
            url: 'order_history_ajax.php',
            type: 'POST',
            data: function(d) {
                d.start_date = $('#startDate').val();
            }
        },
        columns: [
            { 
                data: 'order_datetime',
                render: function(data) {
                    return new Date(data).toLocaleDateString();
                }
            },
            { 
                data: 'order_datetime',
                render: function(data) {
                    return new Date(data).toLocaleTimeString();
                }
            },
            { data: 'order_number' },
            { data: 'items' },
            { 
                data: 'order_total',
                render: function(data) {
                    return '<?php echo $confData['currency']; ?>' + parseFloat(data).toFixed(2);
                }
            },
            {
                data: 'order_id',
                render: function(data) {
                    return `<a href="print_order.php?id=${data}" class="btn btn-sm btn-primary" target="_blank">
                                <i class="fas fa-print"></i> Print
                           </a>`;
                }
            }
        ],
        order: [[0, 'desc'], [1, 'desc']],
        responsive: true
    });

    // Apply date filter
    $('#filterBtn').click(function() {
        table.ajax.reload();
    });

    // Style the search bar for a professional, modern look
    setTimeout(function() {
        $("#orderHistoryTable_filter").css({'float': 'none', 'text-align': 'left', 'margin-left': '0', 'width': '220px', 'height': '38px', 'display': 'flex', 'align-items': 'center', 'background': 'none', 'border': 'none'});
        $("#orderHistoryTable_filter label").addClass('mb-0 d-flex align-items-center').css({'height': '38px'});
        $("#orderHistoryTable_filter input").addClass('form-control form-control-sm ms-2 filter-control').attr({'placeholder': 'Search...', 'style': 'min-width: 120px; max-width: 220px; height: 38px; border-radius: 0.5rem; transition: box-shadow 0.2s, border-color 0.2s;'});
    }, 100);

    // After DataTable initialization
    table.on('draw', function() {
        // Move and style the search bar and length menu after DataTable initialization
        $('.dataTables_length').prependTo('.filter-row').addClass('me-2');
        $('.dataTables_length label').addClass('mb-0 w-100 text-end').css({'font-weight':'500','color':'#8B4543'});
        $('.dataTables_length select').addClass('form-select form-select-sm filter-control ms-2').css({'max-width':'70px','display':'inline-block'});
        $('.dataTables_filter').appendTo('.filter-row');
        $('.dataTables_filter').addClass('ms-auto'); // Push to right
        $('.dataTables_filter label').addClass('mb-0 w-100 text-end');
        $('.dataTables_filter input').addClass('form-control form-control-sm filter-control ms-2').css({'max-width':'180px','display':'inline-block'}).attr('placeholder', 'Search...');
        // Fix: Only bind the input event once per draw, unbind first to prevent stacking
        $('.dataTables_filter input').off('input.dt').on('input.dt', function() {
            table.search(this.value).draw();
        });
    });
});
</script>

<?php include('footer.php'); ?> 