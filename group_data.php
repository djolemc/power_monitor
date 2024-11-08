<?php

$period = $_POST['period'] ?? null;
$type = $_POST['type'] ?? null;

$servername = "mysql-power-monitor-power-monitor.j.aivencloud.com";
$port = 17220;


try {
    $dsn = "mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4";
    $db = new PDO($dsn, $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Priprema SQL upita na osnovu `$type`
    if ($type == 'daily') {
        $query = "SELECT DATE(date) AS day, 
       SUM(power)/60 AS total_power
        FROM power_consumption_tray
        WHERE MONTH(date) = :period
        AND YEAR(date) = YEAR(CURRENT_DATE())
        GROUP BY DATE(date)
        ORDER BY day;";
    } else {
        $query = "SELECT MONTH(date) AS day,
                         ROUND(SUM(power)/60, 2) AS total_power
                  FROM power_consumption_tray
                  WHERE YEAR(date) = :period
                  GROUP BY YEAR(date), MONTH(date)
                  ORDER BY day";
    }


    $stmt = $db->prepare($query);
    $stmt->bindParam(':period', $period, PDO::PARAM_INT);
    $stmt->execute();

    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            $row['day'] => $row['total_power'],
        ];
    }

    echo json_encode($data);
} catch (PDOException $e) {
    echo "Konekcija neuspešna: " . $e->getMessage();
}

$db = null;
?>
