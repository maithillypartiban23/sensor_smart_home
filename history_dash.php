
<?php require_once "auth_check.php"; ?>
<!DOCTYPE html>
<html>

<head>
  <meta charset="UTF-8">
  <title>Smart Home History</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: "Segoe UI", Arial, sans-serif;
    }

    body {
      background: #f0f4f8;
    }

    /* ── Header ── */
    .header {
      background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);
      color: white;
      padding: 0 30px;
      height: 64px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .header-left {
      display: flex;
      align-items: center;
      gap: 14px;
    }

    .header-icon {
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 20px;
    }

    .header-title {
      font-size: 20px;
      font-weight: 700;
      letter-spacing: 0.2px;
    }

    .header-right {
      display: flex;
      align-items: center;
      gap: 16px;
    }

    .header-badge {
      display: flex;
      align-items: center;
      gap: 7px;
      background: rgba(255, 255, 255, 0.12);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 20px;
      padding: 5px 14px;
      font-size: 13px;
      color: rgba(255, 255, 255, 0.9);
    }

    .header-badge.connected {
      color: #4ade80;
    }

    .dot {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: #4ade80;
    }

    /* ── Nav ── */
    .nav {
      background: white;
      border-bottom: 1px solid #e2e8f0;
      padding: 0 30px;
      display: flex;
      gap: 0;
    }

    .nav a {
      text-decoration: none;
      padding: 16px 24px;
      color: #64748b;
      font-size: 14px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 8px;
      border-bottom: 3px solid transparent;
      transition: color 0.2s;
    }

    .nav a.active {
      color: #2563eb;
      border-bottom-color: #2563eb;
      font-weight: 600;
    }

    .nav a:hover {
      color: #2563eb;
    }

    /* ── Panels ── */
    .panel {
      background: white;
      margin: 20px 28px 0;
      padding: 24px 28px;
      border-radius: 16px;
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
    }

    .panel-header {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 20px;
    }

    .panel-header-icon {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      background: #eff6ff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
    }

    .panel-title {
      font-size: 16px;
      font-weight: 700;
      color: #0f172a;
    }

    /* ── Filter form ── */
    .filter-grid {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: 20px;
      margin-bottom: 20px;
    }

    @media (max-width: 700px) {
      .filter-grid {
        grid-template-columns: 1fr;
      }
    }

    .field-label {
      font-size: 12px;
      font-weight: 600;
      color: #64748b;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 7px;
    }

    .input-wrap {
      display: flex;
      align-items: center;
      border: 1.5px solid #e2e8f0;
      border-radius: 10px;
      padding: 0 12px;
      background: white;
      height: 44px;
      gap: 8px;
    }

    .input-wrap:focus-within {
      border-color: #2563eb;
    }

    .input-wrap span {
      font-size: 15px;
      color: #2563eb;
      flex-shrink: 0;
    }

    .input-wrap input {
      border: none;
      outline: none;
      font-size: 14px;
      color: #0f172a;
      background: transparent;
      width: 100%;
      height: 100%;
    }

    .btn-show {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      padding: 11px 24px;
      background: #2563eb;
      border: none;
      border-radius: 10px;
      color: white;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      transition: background 0.2s;
    }

    .btn-show:hover {
      background: #1d4ed8;
    }

    /* ── Chart grid ── */
    .chart-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 8px;
    }

    .chart-header-left {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .chart-header-icon {
      font-size: 16px;
    }

    .chart-header-title {
      font-size: 13px;
      font-weight: 700;
      color: #0f172a;
    }

    .chart-menu {
      color: #94a3b8;
      font-size: 18px;
      cursor: pointer;
      letter-spacing: 2px;
    }

    .chart-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 20px;
      margin-bottom: 8px;
    }

    .chart-box {
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      padding: 16px;
    }

    .chart-box canvas {
      height: 320px !important;
    }

    /* bottom padding */
    .spacer {
      height: 28px;
    }
  </style>
</head>

<body>

  <!-- Header -->
  <div class="header">
    <div class="header-left">
      <div class="header-icon">🏠</div>
      <span class="header-title">Smart Home IoT Monitoring Dashboard</span>
    </div>
     <div class="header-right">
    <div class="header-badge">🕐 <span id="clock">--:-- --</span></div>
    <div class="header-badge connected"><div class="dot"></div> Connected</div>
    <div class="header-badge">👤 <?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
    <div class="header-badge">📡 <?php echo htmlspecialchars($_SESSION['device_id']); ?></div>
    <a href="logout.php" style="text-decoration:none;display:flex;align-items:center;gap:7px;background:rgba(239,68,68,0.15);border:1px solid rgba(239,68,68,0.3);border-radius:20px;padding:5px 14px;font-size:13px;color:#fca5a5;">🚪 Logout</a>
  </div>
  </div>

  <!-- Nav -->
<div class="nav">
  <a href="index.php">⊞ Dashboard</a>
  <a href="history_dash.php" class="active">🕐 History</a>
  <a href="settings.php">⚙ Settings</a>
</div>

  <!-- Filter Panel -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-header-icon">📅</div>
      <span class="panel-title">Select Date &amp; Time Range</span>
    </div>

    <div class="filter-grid">
      <div>
        <div class="field-label">Start Date</div>
        <div class="input-wrap">
          <span>📅</span>
          <input type="date" id="date">
        </div>
      </div>
      <div>
        <div class="field-label">Start Time</div>
        <div class="input-wrap">
          <span>🕐</span>
          <input type="time" id="start" value="00:00">
        </div>
      </div>
      <div>
        <div class="field-label">End Time</div>
        <div class="input-wrap">
          <span>🕐</span>
          <input type="time" id="end" value="23:59">
        </div>
      </div>
    </div>

    <button class="btn-show" onclick="loadHistory()">📊 VIEW HISTORY</button>
  </div>

  <!-- Charts Panel -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-header-icon">📈</div>
      <span class="panel-title">Historical Graphs</span>
    </div>

    <div class="chart-grid">
      <div class="chart-box">
        <div class="chart-header">
          <div class="chart-header-left">
            <span class="chart-header-icon">🌡</span>
            <span class="chart-header-title">Temperature (°C)</span>
          </div>
          <span class="chart-menu">···</span>
        </div>
        <canvas id="tempChart"></canvas>
      </div>
      <div class="chart-box">
        <div class="chart-header">
          <div class="chart-header-left">
            <span class="chart-header-icon">💧</span>
            <span class="chart-header-title">Humidity</span>
          </div>
          <span class="chart-menu">···</span>
        </div>
        <canvas id="humChart"></canvas>
      </div>
      <div class="chart-box">
        <div class="chart-header">
          <div class="chart-header-left">
            <span class="chart-header-icon">🔥</span>
            <span class="chart-header-title">Gas Level</span>
          </div>
          <span class="chart-menu">···</span>
        </div>
        <canvas id="gasChart"></canvas>
      </div>
    </div>
  </div>

  <div class="spacer"></div>

  <!-- Clock -->
  <script>
    function updateClock() {
      const now = new Date();
      let h = now.getHours(), m = now.getMinutes().toString().padStart(2, '0');
      const ampm = h >= 12 ? 'PM' : 'AM'; h = h % 12 || 12;
      document.getElementById('clock').textContent = h + ':' + m + ' ' + ampm;
    }
    updateClock(); setInterval(updateClock, 1000);
  </script>

  <!-- ORIGINAL JAVASCRIPT — UNCHANGED -->
  <script>

    let labels = [];

    let temp = [];

    let hum = [];

    let gas = [];


    function createChart(id, title, data, color) {

      color = color || "#3b82f6";

      return new Chart(

        document.getElementById(id),

        {

          type: "line",

          data: {

            labels: labels,

            datasets: [{

              label: title,

              data: data,

              borderWidth: 3,

              tension: .4,

              borderColor: color,

              backgroundColor: color + "22",

              pointBackgroundColor: color,

              pointRadius: 3,

              pointHoverRadius: 5,

              fill: true

            }]

          },

          options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

              tooltip: {

                enabled: true,

                callbacks: {

                  title: function (context) {

                    return "Time: " + context[0].label;

                  },

                  label: function (context) {

                    return context.dataset.label +

                      ": " +

                      context.raw;

                  }

                }

              }

            },

            scales: {

              x: {

                ticks: { maxTicksLimit: 8, font: { size: 10 }, maxRotation: 45 },

                grid: { color: "#f1f5f9" }

              },

              y: {

                ticks: { font: { size: 11 } },

                grid: { color: "#f1f5f9" }

              }

            }

          }

        }

      );

    }


    let tempChart = createChart(

      "tempChart",

      "Temperature °C",

      temp,

      "#3b82f6"

    );

    let humChart = createChart(

      "humChart",

      "Humidity",

      hum,

      "#22c55e"

    );

    let gasChart = createChart(

      "gasChart",

      "Gas Level",

      gas,

      "#a855f7"

    );


    function loadHistory() {

      let selectedDate =

        document.getElementById("date").value;

      let startTime =

        document.getElementById("start").value;

      let endTime =

        document.getElementById("end").value;


      fetch(

        "history.php?date=" + selectedDate +

        "&start=" + startTime +

        "&end=" + endTime +

        "&t=" + Date.now()

      )

        .then(r => r.json())

        .then(data => {

          labels.length = 0;

          temp.length = 0;

          hum.length = 0;

          gas.length = 0;


          data.forEach(row => {

            let time =

              row.created_at;

            labels.push(time);

            temp.push(

              Number(row.temperature)

            );

            hum.push(

              Number(row.humidity)

            );

            gas.push(

              Number(row.gas_level)

            );

          });


          tempChart.update();

          humChart.update();

          gasChart.update();

        });

    }

  </script>

</body>

</html>