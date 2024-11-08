<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PowerMonitor</title>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="assets/style.css">   

</head>

<body>
    <ul class="nav nav-pills mb-3 mt-2" id="pills-tab" role="tablist">

        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">Current consumption</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">Daily consumption</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="pills-contact-tab" data-bs-toggle="pill" data-bs-target="#pills-contact" type="button" role="tab" aria-controls="pills-contact" aria-selected="false">Monthly consumption</button>
        </li>
    </ul>
    <div class="tab-content" id="pills-tabContent">
        <div class="tab-pane fade show active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab">
        <span style="margin-left:20px">Period:</span>    
        <select style="margin: 0 5px" name="Period" id="period">
                <option value="3">3 hours</option>
                <option selected value="6">6 hours</option>
                <option value="12">12 hours</option>
                <option value="24">24 hours</option>
            </select>
            <canvas style="margin-top:30px; width:90%; height:400px " id="myChart"></canvas>
        </div>
        <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab">
        <span style="margin-left:20px">Select month: </span>  
        <select style="margin: 0 5px" name="Month" id="month">
                <?php
                $currentMonth = date('n');
                $date = new DateTime();
                for ($month = 1; $month <= 12; $month++) {
                    $date->setDate(date('Y'), $month, 1);
                    $monthName = $date->format('F'); // 'F' vraća puno ime meseca (npr. "January")
                    $selected = ($month == $currentMonth) ? 'selected' : '';
                    echo "<option value='$month' $selected>$monthName</option>";
                }
                ?>
            </select>

            <canvas style="margin-top:30px; width:90%; height:400px " id="myChart_daily"></canvas>
        </div>
    </div>
    <div class="tab-pane fade" id="pills-contact" role="tabpanel" aria-labelledby="pills-contact-tab">
    <span style="margin-left:20px">Select year:</span>
        <select style="margin: 0 5px" name="Year" id="year">
            <?php
            $currentYear = date("Y"); // Tekuća godina
            $startYear = 2024;

            for ($year = $startYear; $year <= $currentYear; $year++) {
                $selected = ($year == $currentYear) ? 'selected' : '';
                echo "<option value=\"$year\" $selected>$year</option>";
            }
            ?>
        </select>

        <canvas style="margin-top:30px; width:90%; height:400px " id="myChart_monthly"></canvas>
    </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
        let period = 6; // Početni period od 6 sati
        const ctx = document.getElementById('myChart').getContext('2d');
        const ctx_daily = document.getElementById('myChart_daily').getContext('2d');
        const ctx_monthly = document.getElementById('myChart_monthly').getContext('2d');
        let chart; // Referenca na Chart.js instancu za kasnija ažuriranja
        let chart_daily;
        let chart_monthly;


        function createChart(ctx_chart, period, type) {

            $.ajax({
                url: 'http://powermonitorweb.loc/group_data.php',
                type: 'POST',
                data: {
                    period: period,
                    type: type
                },
                dataType: 'json',
                success: function(response) {
                    // Priprema podataka za grafikon
                    const timeData = [];
                    const consumptionData = [];

                    response.forEach(item => {
                        const date = Object.keys(item)[0];
                        const consumption = item[date];
                        timeData.push(date);
                        consumptionData.push(consumption);
                    });


                    if (type === "daily" && chart_daily) {
                        chart_daily.destroy();
                    } else if (type === "monthly" && chart_monthly) {
                        chart_monthly.destroy();
                    }
                    
                

                    const newChart = new Chart(ctx_chart, {
                        type: 'bar',
                        data: {
                            labels: timeData,
                            datasets: [{
                                label: 'Consumption (Wh)',
                                data: consumptionData,
                                backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                borderColor: 'rgba(54, 162, 235, 1)',
                                borderWidth: 1
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Date' // Oznaka za x osu (dani)
                                    },
                                    ticks: {
                                        autoSkip: true // Ne skipuj oznake na x osi
                                    }
                                },
                                y: {
                                    title: {
                                        display: true,
                                        text: 'Consumption (Wh)' // Oznaka za y osu (potrošnja)
                                    }
                                }
                            }
                        }
                    });
                    if (type === "daily") {
                        chart_daily = newChart;
                    } else {
                        chart_monthly = newChart;
                    }
                },
                error: function(xhr, status, error) {
                    console.error("Došlo je do greške:", error);
                }
            });
        }

        // Funkcija za pokretanje AJAX zahteva i ažuriranje grafikona
        function updateChart() {
            $.ajax({
                url: 'http://powermonitorweb.loc/data.php',
                type: 'POST',
                data: {
                    period: period
                },
                dataType: 'json',
                success: function(response) {
                    // Priprema podataka za grafikon
                    const timeData = response.map(item => item.date);
                    const consumptionData = response.map(item => item.consumption);
                    const insideTempData = response.map(item => item.inside);
                    const outsideTempData = response.map(item => item.outside);

                    // Uništavanje starog grafikona ako postoji
                    if (chart) {
                        chart.destroy();
                    }

                    // Kreiranje novog grafikona sa osveženim podacima
                    chart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: timeData,
                            datasets: [{
                                    label: 'Consumption (W)',
                                    data: consumptionData,
                                    borderColor: 'rgba(54, 162, 235, 1)',
                                    backgroundColor: 'rgba(54, 162, 235, 0.2)',
                                    yAxisID: 'y1',
                                    tension: 0.3,
                                },
                                {
                                    label: 'Room Temperature (°C)',
                                    data: insideTempData,
                                    borderColor: 'rgba(255, 99, 132, 1)',
                                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                    yAxisID: 'y2',
                                    tension: 0.3
                                },
                                {
                                    label: 'Outside Temperature (°C)',
                                    data: outsideTempData,
                                    borderColor: 'rgba(75, 192, 192, 1)',
                                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                                    yAxisID: 'y2',
                                    tension: 0.3
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            pointRadius:1, 
                            pointHoverRadius: 3,
                            maintainAspectRatio: true,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            },
                            scales: {
                                x: {
                                    title: {
                                        display: true,
                                        text: 'Time',
                                    },
                                    ticks: {
                                        callback: function(value, index) {
                                            let timeString = timeData[index];

                                            if (timeString !== undefined) {
                                                let [date, time] = timeString.split(" ");
                                                let [hours, minutes] = time.split(':');
                                                return (minutes === "00") ? `${hours}:${minutes}` : '';
                                            }
                                        },
                                        maxRotation: 0,
                                        autoSkip: false,
                                        maxTicksLimit: 48
                                    }
                                },
                                y1: {
                                    type: 'linear',
                                    position: 'left',
                                    title: {
                                        display: true,
                                        text: 'Consumption (W)'
                                    },
                                    beginAtZero: true,
                                    min: 0,
                                    max: 2000
                                },
                                y2: {
                                    type: 'linear',
                                    position: 'right',
                                    title: {
                                        display: true,
                                        text: 'Temperature (°C)'
                                    },
                                    grid: {
                                        drawOnChartArea: false,
                                    },
                                    beginAtZero: true,
                                    min: -15,
                                    max: 40
                                }
                            }
                        }
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Došlo je do greške:", error);
                }
            });
        }


        $('#period').on('change', function() {
            period = this.value;
            updateChart(); 
        });
        $('#month').on('change', function() {

            month = this.value;
            createChart(ctx_daily, month, "daily"); 
        });

        $('#year').on('change', function() {
            year = this.value;
            createChart(ctx_monthly, year, "monthly"); 
        });

        $('a[data-bs-toggle="pill"]').on('shown.bs.tab', function(e) {
            const targetId = $(e.target).attr('href');

            if (targetId === '#pills-profile') {
                const month = $('#month').val();
                createChart(ctx_daily, month, "daily");
            } else if (targetId === '#pills-contact') {
                const year = $('#year').val();
                createChart(ctx_monthly, year, "monthly");
            }
        });
        // Prvi poziv na početnom periodu
        const d = new Date();
        let month = d.getMonth();
        let year = d.getFullYear();

        updateChart();
        createChart(ctx_daily, month + 1, "daily")
        createChart(ctx_monthly, year, "monthly")

        // Automatski poziv na svakih minut
        setInterval(updateChart, 60000); // 60000 ms = 1 minut
    </script>
   
</body>

</html>