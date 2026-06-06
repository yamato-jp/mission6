<?php
session_start();

// バリデーション（空欄で送信されたら戻す）
if (empty($_POST['investment']) || $_POST['investment'] <= 0 ||
    empty($_POST['start_date']) ||
    empty($_POST['monthly_income']) || $_POST['monthly_income'] <= 0 ||
    empty($_POST['years']) || $_POST['years'] <= 0) {
    header('Location: input.php');
    exit;
}

// POSTデータを変数に格納
$investment     = (float)$_POST['investment'];
$start_date_str = $_POST['start_date']; // "2026-10"
$monthly_income = (float)$_POST['monthly_income'];
$years          = (int)$_POST['years'];

// SESSIONに保存
$_SESSION['investment']     = $investment;
$_SESSION['start_date']     = $start_date_str;
$_SESSION['monthly_income'] = $monthly_income;
$_SESSION['years']          = $years;

// 収入開始日
$start_dt = new DateTime($start_date_str . '-01');

// 計算
$total_months      = $years * 12;
$cumulative_income = 0;
$breakeven_month   = null;
$breakeven_date    = null;
$results           = [];

for ($i = 1; $i <= $total_months; $i++) {
    $cumulative_income += $monthly_income;
    $balance = $cumulative_income - $investment;

    if ($balance >= 0 && $breakeven_month === null) {
        $breakeven_month = $i;
        $bd = clone $start_dt;
        $bd->modify('+' . ($i - 1) . ' months');
        $breakeven_date = $bd->format('Y年m月');
    }

    $current_dt = clone $start_dt;
    $current_dt->modify('+' . ($i - 1) . ' months');

    $results[] = [
        'month'             => $i,
        'date'              => $current_dt->format('Y年m月'),
        'cumulative_income' => $cumulative_income,
        'balance'           => $balance
    ];
}

// 年ごとのサマリー
$yearly_summary = [];
for ($year = 1; $year <= $years; $year++) {
    $idx = ($year * 12) - 1;
    $target_dt = clone $start_dt;
    $target_dt->modify('+' . ($year * 12 - 1) . ' months');

    $yearly_summary[$year] = [
        'cumulative_income' => $results[$idx]['cumulative_income'],
        'balance'           => $results[$idx]['balance'],
        'date'              => $target_dt->format('Y年m月')
    ];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>シミュレーション結果</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <h1>将来ロードマップ作成サービス</h1>
    <div class="container">

        <!-- 入力内容の確認 -->
        <section>
            <h2>入力内容</h2>
            <ul>
                <li>自己投資額：<?php echo $investment; ?> 万円</li>
                <li>収入開始年月：<?php echo $start_dt->format('Y年m月'); ?> から</li>
                <li>月収目標：<?php echo $monthly_income; ?> 万円</li>
                <li>シミュレーション期間：<?php echo $years; ?> 年間</li>
            </ul>
        </section>

        <!-- 投資回収点 -->
        <section>
            <h2>投資回収点</h2>
            <?php if ($breakeven_date !== null): ?>
                <p class="breakeven">
                    収入開始から <?php echo $breakeven_month; ?> ヶ月後
                    （<?php echo $breakeven_date; ?>）にプラスになります！
                </p>
            <?php else: ?>
                <p class="breakeven">シミュレーション期間内に回収できませんでした。</p>
            <?php endif; ?>
        </section>

        <!-- 年ごとのサマリー -->
        <section>
            <h2>年ごとのサマリー</h2>
            <table>
                <tr>
                    <th>時期</th>
                    <th>年月</th>
                    <th>累計収入</th>
                    <th>純利益</th>
                </tr>
                <?php foreach ($yearly_summary as $year => $data): ?>
                <tr>
                    <td><?php echo $year; ?> 年後</td>
                    <td><?php echo $data['date']; ?></td>
                    <td><?php echo $data['cumulative_income']; ?> 万円</td>
                    <td><?php echo $data['balance']; ?> 万円</td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>

        <!-- グラフ -->
        <section>
            <h2>収支推移グラフ</h2>
            <canvas id="myChart" width="800" height="400"></canvas>
        </section>

        <br>
        <a href="download.php" class="btn btn-primary">CSVダウンロード</a>
        <a href="input.php" class="btn btn-secondary">入力に戻る</a>

    </div>

    <script>
    const labels     = <?php echo json_encode(array_column($results, 'date')); ?>;
    const cumulative = <?php echo json_encode(array_column($results, 'cumulative_income')); ?>;
    const balance    = <?php echo json_encode(array_column($results, 'balance')); ?>;
    const investment = <?php echo json_encode(array_fill(0, count($results), (float)$investment)); ?>;

    new Chart(document.getElementById('myChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: '累計収入（万円）',
                    data: cumulative,
                    borderColor: 'rgba(54, 162, 235, 1)',
                    backgroundColor: 'rgba(54, 162, 235, 0.1)',
                    tension: 0.1
                },
                {
                    label: '収支（万円）',
                    data: balance,
                    borderColor: 'rgba(75, 192, 192, 1)',
                    backgroundColor: 'rgba(75, 192, 192, 0.1)',
                    tension: 0.1
                },
                {
                    label: '自己投資額（万円）',
                    data: investment,
                    borderColor: 'rgba(255, 99, 132, 1)',
                    borderDash: [5, 5],
                    tension: 0
                }
            ]
        },
        options: {
            responsive: false,
            scales: {
                y: { title: { display: true, text: '万円' } },
                x: {
                    title: { display: true, text: '年月' },
                    ticks: { maxTicksLimit: 12, maxRotation: 45 }
                }
            }
        }
    });
    </script>

</body>
</html>
