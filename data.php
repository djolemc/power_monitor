<?php

$period = $_POST['period'] ?? null;

$servername = "mysql-power-monitor-power-monitor.j.aivencloud.com";
$port = 17220;


try {
    // Kreiranje PDO konekcije
    $dsn = "mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4";
    $db = new PDO($dsn, $username, $password);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $limit = $period * 60;

    $query = "SELECT date, power, indor_temp, outdoor_temp
              FROM (
                  SELECT date, power, indor_temp, outdoor_temp
                  FROM power_consumption_tray
                  WHERE date >= NOW() - INTERVAL :period HOUR
                  ORDER BY id DESC
                  LIMIT :limit
              ) AS subquery
              ORDER BY date ASC";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':period', $period, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[] = [
            "inside" => $row['indor_temp'],
            "consumption" => $row['power'],
            "outside" => $row['outdoor_temp'],
            "date" => $row['date']
        ];
    }

    echo json_encode($data);
} catch (PDOException $e) {
    echo "Konekcija neuspešna: " . $e->getMessage();
}

$db = null;
?>
