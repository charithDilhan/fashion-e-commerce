<?php
require_once __DIR__ . '/../components/connect.php';
require_seller();
$isSystemAdmin = is_system_admin($conn);

$selectedYear = isset($_GET['year']) && $_GET['year'] !== '' ? (int) $_GET['year'] : null;
$selectedMonth = isset($_GET['month']) && $_GET['month'] !== '' ? (int) $_GET['month'] : null;
$selectedDay = isset($_GET['day']) && $_GET['day'] !== '' ? (int) $_GET['day'] : null;
$selectedStartDay = isset($_GET['start_day']) && $_GET['start_day'] !== '' ? (int) $_GET['start_day'] : null;
$selectedEndDay = isset($_GET['end_day']) && $_GET['end_day'] !== '' ? (int) $_GET['end_day'] : null;

$startDateInput = isset($_GET['start_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($_GET['start_date'])) ? trim($_GET['start_date']) : null;
$endDateInput = isset($_GET['end_date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', trim($_GET['end_date'])) ? trim($_GET['end_date']) : null;

$presetPeriod = clean_text((string) ($_GET['period'] ?? ''), 20);

function build_date_conditions(
    string $colAlias,
    ?int $year,
    ?int $month,
    ?int $day,
    ?int $startDay,
    ?int $endDay,
    ?string $startDate,
    ?string $endDate,
    string $presetPeriod
): array {
    $conds = [];

    // Custom Date Range takes highest precedence if provided
    if ($startDate || $endDate) {
        if ($startDate && $endDate) {
            $s = $startDate . ' 00:00:00';
            $e = $endDate . ' 23:59:59';
            $conds[] = "{$colAlias} BETWEEN '{$s}' AND '{$e}'";
        } elseif ($startDate) {
            $s = $startDate . ' 00:00:00';
            $conds[] = "{$colAlias} >= '{$s}'";
        } elseif ($endDate) {
            $e = $endDate . ' 23:59:59';
            $conds[] = "{$colAlias} <= '{$e}'";
        }
        return $conds;
    }

    if ($year) {
        $conds[] = "YEAR({$colAlias}) = {$year}";
    }
    if ($month) {
        $conds[] = "MONTH({$colAlias}) = {$month}";
    }

    if ($startDay && $endDay) {
        $minD = min($startDay, $endDay);
        $maxD = max($startDay, $endDay);
        $conds[] = "DAY({$colAlias}) BETWEEN {$minD} AND {$maxD}";
    } elseif ($startDay) {
        $conds[] = "DAY({$colAlias}) >= {$startDay}";
    } elseif ($endDay) {
        $conds[] = "DAY({$colAlias}) <= {$endDay}";
    } elseif ($day) {
        $conds[] = "DAY({$colAlias}) = {$day}";
    }

    if ($presetPeriod === 'week1') {
        $conds[] = "DAY({$colAlias}) BETWEEN 1 AND 7";
        if (!$month) $conds[] = "MONTH({$colAlias}) = MONTH(NOW())";
        if (!$year)  $conds[] = "YEAR({$colAlias}) = YEAR(NOW())";
    } elseif ($presetPeriod === 'week2') {
        $conds[] = "DAY({$colAlias}) BETWEEN 8 AND 14";
        if (!$month) $conds[] = "MONTH({$colAlias}) = MONTH(NOW())";
        if (!$year)  $conds[] = "YEAR({$colAlias}) = YEAR(NOW())";
    } elseif ($presetPeriod === 'week3') {
        $conds[] = "DAY({$colAlias}) BETWEEN 15 AND 21";
        if (!$month) $conds[] = "MONTH({$colAlias}) = MONTH(NOW())";
        if (!$year)  $conds[] = "YEAR({$colAlias}) = YEAR(NOW())";
    } elseif ($presetPeriod === 'week4') {
        $conds[] = "DAY({$colAlias}) BETWEEN 22 AND 31";
        if (!$month) $conds[] = "MONTH({$colAlias}) = MONTH(NOW())";
        if (!$year)  $conds[] = "YEAR({$colAlias}) = YEAR(NOW())";
    } elseif (empty($conds)) {
        if ($presetPeriod === 'weekly' || $presetPeriod === '7days') {
            $conds[] = "{$colAlias} >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        } elseif ($presetPeriod === '14days') {
            $conds[] = "{$colAlias} >= DATE_SUB(NOW(), INTERVAL 14 DAY)";
        } elseif ($presetPeriod === '21days') {
            $conds[] = "{$colAlias} >= DATE_SUB(NOW(), INTERVAL 21 DAY)";
        } elseif ($presetPeriod === '28days') {
            $conds[] = "{$colAlias} >= DATE_SUB(NOW(), INTERVAL 28 DAY)";
        } elseif ($presetPeriod === '2months') {
            $conds[] = "{$colAlias} >= DATE_SUB(NOW(), INTERVAL 2 MONTH)";
        } elseif ($presetPeriod === '3months') {
            $conds[] = "{$colAlias} >= DATE_SUB(NOW(), INTERVAL 3 MONTH)";
        } elseif ($presetPeriod === '6months') {
            $conds[] = "{$colAlias} >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
        } elseif ($presetPeriod === 'yearly' || $presetPeriod === '1year') {
            $conds[] = "{$colAlias} >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        } elseif ($presetPeriod === '2years') {
            $conds[] = "{$colAlias} >= DATE_SUB(NOW(), INTERVAL 2 YEAR)";
        } elseif ($presetPeriod === '3years') {
            $conds[] = "{$colAlias} >= DATE_SUB(NOW(), INTERVAL 3 YEAR)";
        } elseif ($presetPeriod === '5years') {
            $conds[] = "{$colAlias} >= DATE_SUB(NOW(), INTERVAL 5 YEAR)";
        } elseif ($presetPeriod === 'all') {
            // All time
        } else {
            $conds[] = "{$colAlias} >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        }
    }
    return $conds;
}

$unprefixedConds = build_date_conditions('created_at', $selectedYear, $selectedMonth, $selectedDay, $selectedStartDay, $selectedEndDay, $startDateInput, $endDateInput, $presetPeriod);
$pConds          = build_date_conditions('p.created_at', $selectedYear, $selectedMonth, $selectedDay, $selectedStartDay, $selectedEndDay, $startDateInput, $endDateInput, $presetPeriod);
$oConds          = build_date_conditions('o.created_at', $selectedYear, $selectedMonth, $selectedDay, $selectedStartDay, $selectedEndDay, $startDateInput, $endDateInput, $presetPeriod);

$dateSql       = $unprefixedConds ? implode(' AND ', $unprefixedConds) : '1=1';
$dateOrdersSql = $unprefixedConds ? implode(' AND ', $unprefixedConds) : '1=1';
$pDateSql      = $pConds ? implode(' AND ', $pConds) : '1=1';
$oDateSql      = $oConds ? implode(' AND ', $oConds) : '1=1';

if ($startDateInput || $endDateInput) {
    if ($startDateInput && $endDateInput) {
        $periodTitle = "Custom Date Range (" . $startDateInput . " to " . $endDateInput . ")";
    } elseif ($startDateInput) {
        $periodTitle = "From Date (" . $startDateInput . " onwards)";
    } else {
        $periodTitle = "Up to Date (" . $endDateInput . ")";
    }
} elseif ($selectedYear || $selectedMonth || $selectedStartDay || $selectedEndDay || $selectedDay) {
    $parts = [];
    if ($selectedYear) $parts[] = "Year " . $selectedYear;
    if ($selectedMonth) {
        $monthNames = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];
        $parts[] = "Month " . ($monthNames[$selectedMonth] ?? $selectedMonth);
    }
    if ($selectedStartDay && $selectedEndDay) {
        $parts[] = "Day " . min($selectedStartDay, $selectedEndDay) . " to Day " . max($selectedStartDay, $selectedEndDay);
    } elseif ($selectedStartDay) {
        $parts[] = "From Day " . $selectedStartDay;
    } elseif ($selectedEndDay) {
        $parts[] = "Up to Day " . $selectedEndDay;
    } elseif ($selectedDay) {
        $parts[] = "Day " . $selectedDay;
    }
    $periodTitle = "Custom Filter (" . implode(", ", $parts) . ")";
} else {
    $titles = [
        'week1'   => '1st Week (Days 1 - 7)',
        'week2'   => '2nd Week (Days 8 - 14)',
        'week3'   => '3rd Week (Days 15 - 21)',
        'week4'   => '4th Week (Days 22 - 31)',
        'weekly'  => 'Weekly (Last 7 Days)',
        '7days'   => 'Last 7 Days',
        '14days'  => 'Last 14 Days (2 Weeks)',
        '21days'  => 'Last 21 Days (3 Weeks)',
        '28days'  => 'Last 28 Days (4 Weeks)',
        'monthly' => 'Monthly (Last 30 Days)',
        '1month'  => 'Last 1 Month',
        '2months' => 'Last 2 Months',
        '3months' => 'Last 3 Months',
        '6months' => 'Last 6 Months',
        'yearly'  => 'Yearly (Last 12 Months)',
        '1year'   => 'Last 1 Year',
        '2years'  => 'Last 2 Years',
        '3years'  => 'Last 3 Years',
        '5years'  => 'Last 5 Years',
        'all'     => 'All Time'
    ];
    $periodTitle = $titles[$presetPeriod] ?? 'Monthly (Last 30 Days)';
}

// Get available years from DB for filter dropdown
$yearsList = [];
try {
    $yRes = $conn->query("SELECT DISTINCT YEAR(created_at) AS yr FROM orders UNION SELECT DISTINCT YEAR(created_at) AS yr FROM products ORDER BY yr DESC")->fetchAll();
    foreach ($yRes as $r) {
        if (!empty($r['yr'])) $yearsList[] = (int) $r['yr'];
    }
} catch (Throwable $e) {}
if (!in_array(2026, $yearsList, true)) $yearsList[] = 2026;
if (!in_array(2025, $yearsList, true)) $yearsList[] = 2025;
rsort($yearsList);

function get_report_count(PDO $conn, string $table, string $whereClause): int {
    $sql = "SELECT COUNT(*) FROM {$table}";
    if ($whereClause && $whereClause !== '1=1') {
        $sql .= " WHERE {$whereClause}";
    }
    $stmt = $conn->query($sql);
    return (int) $stmt->fetchColumn();
}

// 1. All Products
$pWhere = [];
if (!$isSystemAdmin) $pWhere[] = "seller_id = " . $conn->quote($seller_id);
if ($dateSql !== '1=1') $pWhere[] = $dateSql;
$allProducts = get_report_count($conn, 'products', implode(' AND ', $pWhere));

// 2. Active Products
$actWhere = ["status = 'active'"];
if (!$isSystemAdmin) $actWhere[] = "seller_id = " . $conn->quote($seller_id);
if ($dateSql !== '1=1') $actWhere[] = $dateSql;
$activeProducts = get_report_count($conn, 'products', implode(' AND ', $actWhere));

// 3. Total Orders
$oWhere = [];
if (!$isSystemAdmin) $oWhere[] = "seller_id = " . $conn->quote($seller_id);
if ($dateOrdersSql !== '1=1') $oWhere[] = $dateOrdersSql;
$totalOrders = get_report_count($conn, 'orders', implode(' AND ', $oWhere));

// 4. Pending Orders
$pendWhere = ["status IN ('in progress','preparing','delivering','confirmed')"];
if (!$isSystemAdmin) $pendWhere[] = "seller_id = " . $conn->quote($seller_id);
if ($dateOrdersSql !== '1=1') $pendWhere[] = $dateOrdersSql;
$pendingOrders = get_report_count($conn, 'orders', implode(' AND ', $pendWhere));

// 5. Paid Revenue
$revWhere = ["payment_status = 'paid'", "status <> 'canceled'"];
if (!$isSystemAdmin) $revWhere[] = "seller_id = " . $conn->quote($seller_id);
if ($dateOrdersSql !== '1=1') $revWhere[] = $dateOrdersSql;
$revSql = "SELECT COALESCE(SUM(price * qty), 0) FROM orders WHERE " . implode(' AND ', $revWhere);
$paidRevenue = (float) $conn->query($revSql)->fetchColumn();

// 6. Customers
$cWhere = ($dateSql !== '1=1') ? $dateSql : '';
$customersCount = $isSystemAdmin ? get_report_count($conn, 'users', $cWhere) : 0;

// 7. Messages
$mWhere = ($dateSql !== '1=1') ? $dateSql : '';
$messagesCount = $isSystemAdmin ? get_report_count($conn, 'message', $mWhere) : 0;

// 8. Sellers
$sWhere = ($dateSql !== '1=1') ? $dateSql : '';
$sellersCount = $isSystemAdmin ? get_report_count($conn, 'sellers', $sWhere) : 0;

// 9. Cart Records
$cartWhere = ($dateSql !== '1=1') ? $dateSql : '';
$cartCount = $isSystemAdmin ? get_report_count($conn, 'cart', $cartWhere) : 0;

// 10. Wishlist Records
$wishWhere = ($dateSql !== '1=1') ? $dateSql : '';
$wishlistCount = $isSystemAdmin ? get_report_count($conn, 'wishlist', $wishWhere) : 0;

// Category Breakdown Data
$catQuery = "
    SELECT 
        COALESCE(p.category, 'Uncategorized') AS category_name,
        COUNT(DISTINCT p.id) AS product_count,
        COUNT(o.id) AS order_count,
        COALESCE(SUM(CASE WHEN o.payment_status = 'paid' AND o.status <> 'canceled' THEN o.price * o.qty ELSE 0 END), 0) AS total_revenue
    FROM products p
    LEFT JOIN orders o ON o.product_id = p.id " . ($oDateSql !== '1=1' ? "AND {$oDateSql}" : "") . "
    WHERE 1=1 " . (!$isSystemAdmin ? "AND p.seller_id = " . $conn->quote($seller_id) : "") . "
    " . ($pDateSql !== '1=1' ? "AND {$pDateSql}" : "") . "
    GROUP BY p.category
    ORDER BY total_revenue DESC, product_count DESC
";
$catResults = $conn->query($catQuery)->fetchAll();

$catNames = [];
$catRevenues = [];
$catOrders = [];
$catProducts = [];

foreach ($catResults as $cat) {
    $catNames[] = $cat['category_name'];
    $catRevenues[] = (float) $cat['total_revenue'];
    $catOrders[] = (int) $cat['order_count'];
    $catProducts[] = (int) $cat['product_count'];
}

if (empty($catNames)) {
    $catNames = ['No Data'];
    $catRevenues = [0];
    $catOrders = [0];
    $catProducts = [0];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Analytics Reports | Admin</title>
    <link rel="stylesheet" href="<?= e(app_url('css/admin_style.css')) ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .report-header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }
        .filter-panel {
            background: #fff;
            padding: 20px 24px;
            border-radius: 16px;
            border: 1px solid var(--line);
            margin-bottom: 24px;
            box-shadow: var(--shadow);
        }
        .filter-section-title {
            font-weight: 700;
            font-size: 0.88rem;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .preset-row {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            align-items: center;
        }
        .preset-group {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        .preset-pills {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .pill-btn {
            padding: 6px 14px;
            border-radius: 999px;
            border: 1px solid var(--line);
            background: var(--soft);
            color: var(--ink);
            font-weight: 600;
            font-size: 0.82rem;
            text-decoration: none;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .pill-btn:hover, .pill-btn.active {
            background: var(--ink);
            color: #fff;
            border-color: var(--ink);
        }
        .filter-form-row {
            display: flex;
            flex-wrap: wrap;
            gap: 14px;
            align-items: flex-end;
            margin-top: 14px;
        }
        .filter-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .filter-group label {
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--muted);
        }
        .filter-group select, .filter-group input[type="date"] {
            padding: 9px 14px;
            border-radius: 10px;
            border: 1px solid var(--line);
            background: #fff;
            font-size: 0.9rem;
            color: var(--ink);
            min-width: 140px;
            font-family: inherit;
        }
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 22px;
            margin-top: 24px;
        }
        .chart-card {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 22px;
            box-shadow: var(--shadow);
        }
        .chart-card.full-width {
            grid-column: 1 / -1;
        }
        .chart-card h3 {
            margin: 0 0 16px;
            font-size: 1.15rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .chart-container {
            position: relative;
            height: 320px;
            width: 100%;
        }
        .print-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #222;
            color: #fff;
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .print-btn:hover {
            opacity: 0.9;
        }

        /* PRINT STYLES */
        @media print {
            .admin-topbar, .admin-sidebar, .filter-panel, .no-print, button, .admin-menu-toggle, .admin-footer {
                display: none !important;
            }
            .admin-main {
                margin: 0 !important;
                padding: 10px !important;
            }
            body {
                background: #fff !important;
                color: #000 !important;
            }
            .stat-card, .chart-card, .panel {
                box-shadow: none !important;
                border: 1px solid #ccc !important;
                break-inside: avoid;
            }
            .chart-grid {
                grid-template-columns: 1fr !important;
            }
            .chart-card.full-width {
                grid-column: auto !important;
            }
            .chart-container {
                height: 250px !important;
            }
        }
    </style>
</head>
<body>
<?php include __DIR__ . '/../components/admin_header.php'; ?>

<main class="admin-main">
    <div class="admin-container">
        
        <div class="report-header-row">
            <div>
                <h1>Analytics Reports</h1>
                <p>Showing report for: <strong><?= e($periodTitle) ?></strong></p>
            </div>
            <button type="button" class="print-btn no-print" onclick="window.print()">
                🖨️ Print / Save PDF Report
            </button>
        </div>

        <!-- ADVANCED TIMEFRAME FILTER PANEL -->
        <div class="filter-panel no-print">
            <form method="get" class="filter-form-row" style="margin-top:0;">
                <!-- Date Pickers -->
                <div class="filter-group">
                    <label>Start Date</label>
                    <input type="date" name="start_date" value="<?= e($startDateInput ?? '') ?>">
                </div>
                <div class="filter-group">
                    <label>End Date</label>
                    <input type="date" name="end_date" value="<?= e($endDateInput ?? '') ?>">
                </div>

                <!-- Year & Month -->
                <div class="filter-group">
                    <label>Year</label>
                    <select name="year">
                        <option value="">All Years</option>
                        <?php foreach ($yearsList as $yr): ?>
                            <option value="<?= $yr ?>" <?= $selectedYear === $yr ? 'selected' : '' ?>><?= $yr ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label>Month</label>
                    <select name="month">
                        <option value="">All Months</option>
                        <?php
                        $mList = [1=>'January',2=>'February',3=>'March',4=>'April',5=>'May',6=>'June',7=>'July',8=>'August',9=>'September',10=>'October',11=>'November',12=>'December'];
                        foreach ($mList as $num => $mName):
                        ?>
                            <option value="<?= $num ?>" <?= $selectedMonth === $num ? 'selected' : '' ?>><?= $num ?> - <?= $mName ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                        <!-- Day Range -->
                        <div class="filter-group">
                            <label>From Day</label>
                            <select name="start_day">
                                <option value="">All Days / Day 1</option>
                                <?php for ($d = 1; $d <= 31; $d++): ?>
                                    <option value="<?= $d ?>" <?= $selectedStartDay === $d ? 'selected' : '' ?>>Day <?= $d ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label>To Day</label>
                            <select name="end_day">
                                <option value="">Day 31</option>
                                <?php for ($d = 1; $d <= 31; $d++): ?>
                                    <option value="<?= $d ?>" <?= $selectedEndDay === $d ? 'selected' : '' ?>>Day <?= $d ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <div style="display:flex; gap:8px; align-items:flex-end;">
                            <button type="submit" class="button" style="padding:9px 18px;">Apply Filter</button>
                            <a href="reports.php" class="button secondary" style="padding:9px 16px;">Reset</a>
                        </div>
                    </form>
        </div>

        <!-- METRICS OVERVIEW STATS -->
        <div class="stats-grid">
            <div class="stat-card">
                <span>All Products</span>
                <strong><?= $allProducts ?></strong>
            </div>
            <div class="stat-card">
                <span>Active Products</span>
                <strong><?= $activeProducts ?></strong>
            </div>
            <div class="stat-card">
                <span>Total Orders</span>
                <strong><?= $totalOrders ?></strong>
            </div>
            <div class="stat-card">
                <span>Pending Orders</span>
                <strong><?= $pendingOrders ?></strong>
            </div>
            <div class="stat-card">
                <span>Paid Revenue</span>
                <strong><?= e(money($paidRevenue)) ?></strong>
            </div>

            <?php if ($isSystemAdmin): ?>
                <div class="stat-card">
                    <span>Customers</span>
                    <strong><?= $customersCount ?></strong>
                </div>
                <div class="stat-card">
                    <span>Messages</span>
                    <strong><?= $messagesCount ?></strong>
                </div>
                <div class="stat-card">
                    <span>Sellers</span>
                    <strong><?= $sellersCount ?></strong>
                </div>
                <div class="stat-card">
                    <span>Cart Records</span>
                    <strong><?= $cartCount ?></strong>
                </div>
                <div class="stat-card">
                    <span>Wishlist Records</span>
                    <strong><?= $wishlistCount ?></strong>
                </div>
            <?php endif; ?>
        </div>

        <!-- CATEGORY DATA TABLE -->
        <section class="panel" style="margin-top: 24px;">
            <h2>Category Breakdown Table</h2>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Category</th>
                            <th>Products Count</th>
                            <th>Total Orders</th>
                            <th>Paid Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($catResults as $cRow): ?>
                            <tr>
                                <td><strong><?= e($cRow['category_name']) ?></strong></td>
                                <td><?= (int)$cRow['product_count'] ?></td>
                                <td><?= (int)$cRow['order_count'] ?></td>
                                <td><strong><?= e(money((float)$cRow['total_revenue'])) ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- CATEGORY BAR CHARTS -->
        <div class="chart-grid">
            
            <!-- BAR CHART 1: REVENUE BY CATEGORY -->
            <div class="chart-card full-width">
                <h3>
                    <span>Category Paid Revenue (Rs.)</span>
                    <small style="color:var(--muted); font-weight:normal; font-size:0.85rem;"><?= e($periodTitle) ?></small>
                </h3>
                <div class="chart-container">
                    <canvas id="categoryRevenueChart"></canvas>
                </div>
            </div>

            <!-- BAR CHART 2: ORDERS BY CATEGORY -->
            <div class="chart-card">
                <h3>
                    <span>Category Total Orders</span>
                    <small style="color:var(--muted); font-weight:normal; font-size:0.85rem;"><?= e($periodTitle) ?></small>
                </h3>
                <div class="chart-container">
                    <canvas id="categoryOrdersChart"></canvas>
                </div>
            </div>

            <!-- BAR CHART 3: PRODUCTS BY CATEGORY -->
            <div class="chart-card">
                <h3>
                    <span>Category Products Count</span>
                    <small style="color:var(--muted); font-weight:normal; font-size:0.85rem;"><?= e($periodTitle) ?></small>
                </h3>
                <div class="chart-container">
                    <canvas id="categoryProductsChart"></canvas>
                </div>
            </div>

        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const labels = <?= json_encode($catNames) ?>;
    const revenues = <?= json_encode($catRevenues) ?>;
    const orders = <?= json_encode($catOrders) ?>;
    const products = <?= json_encode($catProducts) ?>;

    const brandColors = [
        '#e59a2f', '#1f7a4f', '#2563eb', '#7c3aed', '#db2777', 
        '#059669', '#d97706', '#4f46e5', '#ca8a04', '#0284c7'
    ];

    // Chart 1: Revenue by Category
    new Chart(document.getElementById('categoryRevenueChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Paid Revenue (Rs.)',
                data: revenues,
                backgroundColor: brandColors,
                borderRadius: 8,
                maxBarThickness: 45
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ' Revenue: Rs. ' + ctx.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: '#f0ede6' }
                },
                x: { grid: { display: false } }
            }
        }
    });

    // Chart 2: Orders by Category
    new Chart(document.getElementById('categoryOrdersChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Total Orders',
                data: orders,
                backgroundColor: '#1f7a4f',
                borderRadius: 8,
                maxBarThickness: 35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: '#f0ede6' }
                },
                x: { grid: { display: false } }
            }
        }
    });

    // Chart 3: Products by Category
    new Chart(document.getElementById('categoryProductsChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Products Count',
                data: products,
                backgroundColor: '#2563eb',
                borderRadius: 8,
                maxBarThickness: 35
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 },
                    grid: { color: '#f0ede6' }
                },
                x: { grid: { display: false } }
            }
        }
    });
});
</script>
</body>
</html>
