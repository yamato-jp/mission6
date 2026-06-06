<?php session_start(); ?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8" />
    <title>将来ロードマップ作成サービス</title>
    <link rel="stylesheet" href="style.css">
  </head>
  <body>
    <h1>将来ロードマップ作成サービス</h1>
    <div class="container">
      <form action="result.php" method="post">

          
         <!-- 自己投資額だけ min="1" をつける（マイナス・0を入力不可）-->
         自己投資額:
          <input type="number" name="investment" placeholder="例：100" min="1">
          万円
          <br><br>

          収入開始年月:
          <input type="month" name="start_date">
          から
          <br><br>

          月収目標:
          <input type="number" name="monthly_income" placeholder="例：10" min="1">
          万円
          <br><br>

          シミュレーション期間:
          <input type="number" name="years" placeholder="例：3" min="1" max="30">
          年間
          <br><br>

          <input type="submit" name="submit" value="ロードマップを作成する">

      </form>
    </div>
  </body>
</html>