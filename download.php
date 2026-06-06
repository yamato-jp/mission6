<?php
session_start();

// SESSIONからデータを取得
$investment     = $_SESSION['investment'];
$start_date_str = $_SESSION['start_date'];
$monthly_income = $_SESSION['monthly_income'];
$years          = $_SESSION['years'];

// 収入開始日
$start_dt = new DateTime($start_date_str . '-01');

// 計算
$total_months      = $years * 12;
$cumulative_income = 0;
$results           = [];

for ($i = 1; $i <= $total_months; $i++) {
    $cumulative_income += $monthly_income;
    $balance = $cumulative_income - $investment;

    $current_dt = clone $start_dt;
    $current_dt->modify('+' . ($i - 1) . ' months');

    $results[] = [
        'date'              => $current_dt->format('Y年m月'),
        'cumulative_income' => $cumulative_income,
        'balance'           => $balance
    ];
}

// CSVとして出力
header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="roadmap.csv"');
echo "\xEF\xBB\xBF"; // Excel文字化け防止

$fp = fopen('php://output', 'w');
fputcsv($fp, ['年月', '累計収入（万円）', '純利益（万円）']);

foreach ($results as $row) {
    fputcsv($fp, [
        $row['date'],
        $row['cumulative_income'],
        $row['balance']
    ]);
}

fclose($fp);
?>