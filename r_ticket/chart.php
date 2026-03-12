<?php
require_once 'config.php';
requireLogin();

$conn = getConnection();

// Level user: 0=user, 2=eksekutor, 1=admin
$user_level = $_SESSION['admin'] ?? 0;
$nip = $_SESSION['nip'] ?? '';

$where = [];
$params = [];
$types = '';

if($user_level == 0){ 
    $where[] = "nip = ?";
    $params[] = $nip;
    $types .= 's';
} elseif($user_level == 2){ 
    $where[] = "eksekutor = ?";
    $params[] = $nip;
    $types .= 's';
}

// Default filter tanggal
$from_date = $_GET['from'] ?? date('Y-m-01');
$to_date   = $_GET['to'] ?? date('Y-m-d');

// Fungsi ambil chart data
function getChartData($conn, $where, $types, $params, $from_date, $to_date) {
    $query = "SELECT DATE(date) as tgl, COUNT(*) as total 
              FROM ticket" . ($where ? " WHERE ".implode(' AND ', $where)." AND " : " WHERE ") . "DATE(date) BETWEEN ? AND ? 
              GROUP BY DATE(date) ORDER BY DATE(date) ASC";

    $param_types = $types . 'ss';
    $param_values = array_merge($params, [$from_date, $to_date]);

    $stmt = $conn->prepare($query);
    if(!empty($param_values)){
        $refs = [];
        foreach($param_values as $key => $value){
            $refs[$key] = &$param_values[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], array_merge([$param_types], $refs));
    }
    $stmt->execute();
    $result = $stmt->get_result();

    $labels = [];
    $data = [];
    while($row = $result->fetch_assoc()){
        $labels[] = $row['tgl'];
        $data[] = $row['total'];
    }
    return ['labels' => $labels, 'data' => $data];
}

// Ambil chart & summary default
$chartData = getChartData($conn, $where, $types, $params, $from_date, $to_date);

$summary_query = "
SELECT 
    t.eksekutor,
    u.fullname,

    -- total bulan ini
    SUM(
        CASE 
            WHEN MONTH(t.date) = MONTH(CURDATE()) 
             AND YEAR(t.date) = YEAR(CURDATE()) 
            THEN 1 ELSE 0 
        END
    ) AS total_bulan,

    -- total hari ini
    SUM(
        CASE 
            WHEN DATE(t.date) = CURDATE() 
            THEN 1 ELSE 0 
        END
    ) AS total_hari,

    -- status bulan ini
    SUM(
        CASE 
            WHEN t.status_pekerjaan = 'on progress'
             AND MONTH(t.date) = MONTH(CURDATE())
             AND YEAR(t.date) = YEAR(CURDATE())
            THEN 1 ELSE 0 
        END
    ) AS on_progress,

    SUM(
        CASE 
            WHEN t.status_pekerjaan = 'complete'
             AND MONTH(t.date) = MONTH(CURDATE())
             AND YEAR(t.date) = YEAR(CURDATE())
            THEN 1 ELSE 0 
        END
    ) AS complete,

    SUM(
        CASE 
            WHEN t.status_pekerjaan = 'pending'
             AND MONTH(t.date) = MONTH(CURDATE())
             AND YEAR(t.date) = YEAR(CURDATE())
            THEN 1 ELSE 0 
        END
    ) AS pending

FROM ticket t
LEFT JOIN users u ON t.eksekutor = u.nip
";


if($user_level < 1){
    $summary_query .= " WHERE t.eksekutor='$nip'";
}

$summary_query .= "
GROUP BY t.eksekutor, u.fullname
ORDER BY u.fullname ASC
";


$summary_result = $conn->query($summary_query);
$summaries = $summary_result->fetch_all(MYSQLI_ASSOC);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Realtime</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
<link rel="stylesheet" href="chart.css">
<!-- <style>
</style> -->
</head>
<body>
<?php include 'navbar.php'; ?>
<div id="app-content">
<div class="" style="margin-bottom:60px;">
    <!-- Filter realtime -->
    <div class="card filter-bar">
        <input type="date" id="fromDate" value="<?= htmlspecialchars($from_date) ?>" placeholder="From">
        <input type="date" id="toDate" value="<?= htmlspecialchars($to_date) ?>" placeholder="To">
    </div>

    <!-- Chart -->
    <div class="card">
        <h2>Traffic Tiket</h2>
        <canvas id="trafficChart"></canvas>
    </div>

    <!-- Summary Eksekutor -->
    <div class="card">
        <h2>Progress Eksekutor</h2>
        <div class="summary-cards" id="summaryCards">
            <?php foreach($summaries as $s): ?>
            <div class="summary-card">
                <div class="summary-card-left">
                    <h3><?= $s['fullname'] ? $s['fullname'] : $s['eksekutor'] ?></h3>
                    <p>Total Bulan Ini: <strong><?= $s['total_bulan'] ?></strong></p>
                    <p>Total Hari Ini: <strong><?= $s['total_hari'] ?></strong></p>
                </div>
                <div class="summary-card-right">
                    <div class="badge">On: <?= $s['on_progress'] ?></div>
                    <div class="badge">Complete: <?= $s['complete'] ?></div>
                    <div class="badge">Pending: <?= $s['pending'] ?></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</div>
<script>
let ctx = document.getElementById('trafficChart').getContext('2d');
let trafficChart = new Chart(ctx, {
    type:'line',
    data:{
        labels: <?= json_encode($chartData['labels']) ?>,
        datasets:[{
            label:'Jumlah Tiket',
            data: <?= json_encode($chartData['data']) ?>,
            backgroundColor:'rgb(197, 216, 245)',
            borderColor:'#aecbf7ff',
            borderWidth:3,
            tension:0.4,
            fill:true,
            pointBackgroundColor:'#aecbf7ff',
            pointBorderColor:'#fff',
            pointHoverRadius:6,
            pointRadius:5,
        }]
    },
    options:{
        responsive:true,
        maintainAspectRatio:false,
        plugins:{
            legend:{ display:true, position:'top', labels:{color:'#1c1c1e', font:{weight:'600'}} },
            tooltip:{ mode:'index', intersect:false, backgroundColor:'#fff', titleColor:'#1c1c1e', bodyColor:'#1c1c1e', borderColor:'#ccc', borderWidth:1, padding:10 }
        },
        scales:{
            x:{ title:{display:true,text:'Tanggal', color:'#8e8e93', font:{weight:'500'}}, ticks:{color:'#8e8e93', autoSkip:false} },
            y:{ beginAtZero:true, title:{display:true,text:'Jumlah', color:'#8e8e93', font:{weight:'500'}}, ticks:{color:'#8e8e93'} }
        }
    }
});

// Realtime filter
document.getElementById('fromDate').addEventListener('change', updateData);
document.getElementById('toDate').addEventListener('change', updateData);

function updateData(){
    let from = document.getElementById('fromDate').value;
    let to = document.getElementById('toDate').value;
    fetch(`chart_ajax.php?from=${from}&to=${to}`)
        .then(res => res.json())
        .then(res => {
            // Update chart
            trafficChart.data.labels = res.labels;
            trafficChart.data.datasets[0].data = res.data;
            trafficChart.update();

            // Update summary
            let summaryDiv = document.getElementById('summaryCards');
            summaryDiv.innerHTML = '';
            res.summaries.forEach(s=>{
                summaryDiv.innerHTML += `
                <div class="summary-card">
                    <div class="summary-card-left">
                        <h3>${s.fullname || s.eksekutor}</h3>
                        <p>Total Bulan Ini: <strong>${s.total_bulan}</strong></p>
                        <p>Total Hari Ini: <strong>${s.total_hari}</strong></p>
                    </div>
                    <div class="summary-card-right">
                        <div class="badge">On: ${s.on_progress}</div>
                        <div class="badge">Complete: ${s.complete}</div>
                        <div class="badge">Pending: ${s.pending}</div>
                    </div>
                </div>`;
            });
        });
}
</script>
</body>
</html>
