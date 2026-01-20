<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Data Center Monitoring</title>
<meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.1/dist/css/bootstrap.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<style>
:root {
  --bg:#0b1220;
  --card:#111827;
  --border:#1f2937;
  --text:#e5e7eb;
  --muted:#9ca3af;
  --green:#22c55e;
  --red:#ef4444;
  --yellow:#eab308;
}

body {
  background: var(--bg);
  color: var(--text);
  font-family: 'Inter', system-ui;
  padding: 30px;
}

h2 { font-weight:700; }
.subtitle { color:var(--muted); font-size:14px; }

.dashboard {
  display:grid;
  grid-template-columns:repeat(auto-fit,minmax(340px,1fr));
  gap:24px;
  margin-top:30px;
}

/* ===== CARDS ===== */
.card {
  background:linear-gradient(180deg,#0f172a,var(--card));
  border:1px solid var(--border);
  border-radius:18px;
  padding:24px;
  box-shadow:0 25px 50px rgba(0,0,0,.45);
}

.card-title {
  font-size:14px;
  font-weight:600;
  color:var(--muted);
  letter-spacing:.1em;
  text-transform:uppercase;
  margin-bottom:12px;
}

/* ===== BIG METRICS ===== */
.big-metric {
  text-align:center;
  padding:40px 10px;
}

.big-value {
  font-size:64px;
  font-weight:700;
  line-height:1;
}

.big-unit {
  font-size:22px;
  color:var(--muted);
}

.big-sub {
  margin-top:10px;
  font-size:14px;
  color:var(--muted);
}

/* ===== STATUS ===== */
.status-pill {
  padding:6px 14px;
  border-radius:999px;
  font-size:13px;
  font-weight:500;
}

.ok { background:rgba(34,197,94,.15); color:var(--green); }
.bad { background:rgba(239,68,68,.15); color:var(--red); }
.warn { background:rgba(234,179,8,.15); color:var(--yellow); }

/* ===== LISTS ===== */
.list-item {
  padding:10px 0;
  border-bottom:1px dashed var(--border);
  font-size:15px;
}
.list-item:last-child { border-bottom:none; }
</style>
</head>

<body>

<div class="text-center">
  <h2>Data Center Monitoring</h2>
  <div class="subtitle">Live Infrastructure Overview</div>
</div>

<div class="dashboard">

  <!-- TEMPERATURE (BIG) -->
  <div class="card">
    <div class="card-title">Temperature</div>
    <div class="big-metric">
      <div class="big-value" id="temperature">--</div>
      <div class="big-unit">°C</div>
      <div class="big-sub">Server Room</div>
    </div>
  </div>

  <!-- HUMIDITY (BIG) -->
  <div class="card">
    <div class="card-title">Humidity</div>
    <div class="big-metric">
      <div class="big-value" id="humidity">--</div>
      <div class="big-unit">%</div>
      <div class="big-sub">Environment</div>
    </div>
  </div>

  <!-- CONNECTIVITY -->
  <div class="card">
    <div class="card-title">Connectivity</div>

    <div class="list-item">
      EthioTelecom
      <span id="ethio-status" class="status-pill warn float-right">Checking</span>
    </div>

    <div class="list-item">
      Safaricom
      <span id="safaricom-status" class="status-pill warn float-right">Checking</span>
    </div>
  </div>

  <!-- SERVER STATUS -->
  <div class="card">
    <div class="card-title">Servers</div>
    <div id="server-status-box">Loading...</div>
  </div>

  <!-- HIGH UTILIZATION -->
  <div class="card">
    <div class="card-title">High Utilization (&gt;70%)</div>
    <div id="high-utilization-list">Loading...</div>
  </div>

  <!-- CAMERAS -->
  <div class="card">
    <div class="card-title">Cameras</div>
    <div id="camera-status" class="status-pill ok">All Cameras Online</div>
  </div>

  <!-- INTERNET -->
  <div class="card">
    <div class="card-title">Internet Performance</div>
    <div class="list-item">Latency <strong id="latency" class="float-right">-- ms</strong></div>
    <div class="list-item">Jitter <strong id="jitter" class="float-right">-- ms</strong></div>
    <div class="list-item">Download <strong id="download" class="float-right">-- Mbps</strong></div>
    <div class="list-item">Upload <strong id="upload" class="float-right">-- Mbps</strong></div>
  </div>

</div>

<script>
/* ===== TEMP + HUMIDITY (NO ANIMATION) ===== */
function updateData() {
  $.getJSON('getdata.php', function(r) {

    $('#temperature').text(parseFloat(r.temperature).toFixed(1));
    $('#humidity').text(parseFloat(r.humidity).toFixed(1));

    $('#ethio-status')
      .text(r.EthioTelecom_Status)
      .removeClass('ok bad warn')
      .addClass(r.EthioTelecom_Status === 'Online' ? 'ok' : 'bad');

    $('#safaricom-status')
      .text(r.Safaricom_Status)
      .removeClass('ok bad warn')
      .addClass(r.Safaricom_Status === 'Online' ? 'ok' : 'bad');
  });
}

/* ===== CAMERAS ===== */
function checkCameraStatus() {
  fetch('checkmk-project/checkmk.php')
  .then(r=>r.json())
  .then(d=>{
    const el=document.getElementById('camera-status');
    if(d.down.length===0){
      el.className='status-pill ok';
      el.innerText='All Cameras Online';
    } else {
      el.className='status-pill bad';
      el.innerText=d.down.join(', ');
    }
  });
}

/* ===== SERVERS ===== */
function checkServerStatus() {
  fetch('server_status.php')
  .then(r=>r.json())
  .then(d=>{
    const offline=d.servers.filter(s=>!s.online);
    const box=document.getElementById('server-status-box');
    if(!offline.length){
      box.innerHTML='<span class="status-pill ok">All Servers Online</span>';
    } else {
      box.innerHTML=offline.map(s=>`<div class="list-item">${s.name} (${s.ip})</div>`).join('');
    }
  });
}

/* ===== HIGH UTILIZATION ===== */
function updateHighUtilization() {
  $.getJSON('zabbix_monitor.php', function(data){
    let out='';
    for(let s in data){
      let c=parseFloat(data[s]["CPU Utilization"]);
      let m=parseFloat(data[s]["Memory Utilization"]);
      let d=parseFloat(data[s]["Disk Utilization"]);
      if(c>70||m>70||d>70){
        out+=`<div class="list-item"><strong>${s}</strong> | CPU ${c}% RAM ${m}% Disk ${d}%</div>`;
      }
    }
    $('#high-utilization-list').html(out || '<span class="status-pill ok">All Normal</span>');
  });
}

/* ===== INTERNET SPEED ===== */
async function speedTest(){
  const start=performance.now();
  await fetch("https://www.cloudflare.com/cdn-cgi/trace",{cache:"no-store"});
  $('#latency').text((performance.now()-start).toFixed(1)+' ms');

  const dStart=performance.now();
  await fetch("https://speed.cloudflare.com/__down?bytes=25000000",{cache:"no-store"});
  $('#download').text(((25*8)/((performance.now()-dStart)/1000)).toFixed(2)+' Mbps');
}

/* ===== INIT ===== */
updateData();
checkCameraStatus();
checkServerStatus();
updateHighUtilization();
speedTest();

setInterval(updateData,2000);
setInterval(checkCameraStatus,3000);
setInterval(checkServerStatus,3000);
setInterval(updateHighUtilization,5000);
setInterval(speedTest,300000);
</script>

</body>
</html>
