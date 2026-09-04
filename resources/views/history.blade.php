<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Sensor — SmartGarden</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-base:       #050d0a;
            --bg-card:       rgba(10, 25, 18, 0.75);
            --border:        rgba(34, 197, 94, 0.12);
            --green-400:  #4ade80;
            --green-500:  #22c55e;
            --green-600:  #16a34a;
            --blue-400:   #60a5fa;
            --amber-400:  #fbbf24;
            --red-400:    #f87171;
            --text-primary:   #f0fdf4;
            --text-muted:     #4ade8088;
            --radius-md:  14px;
            --radius-lg:  20px;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-base);
            color: var(--text-primary);
            min-height: 100vh;
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -20%, rgba(34,197,94,0.08) 0%, transparent 70%),
                radial-gradient(ellipse 60% 40% at 80% 100%, rgba(59,130,246,0.05) 0%, transparent 60%);
        }
        .app-wrapper { max-width: 1000px; margin: 0 auto; padding: 24px 16px; }
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
        .btn-back { display: inline-flex; align-items: center; gap: 8px; background: rgba(34,197,94,0.1); color: var(--green-400); border: 1px solid var(--border); padding: 10px 16px; border-radius: var(--radius-md); text-decoration: none; font-size: 0.85rem; font-weight: 600; transition: all 0.2s ease; }
        .btn-back:hover { background: rgba(34,197,94,0.2); border-color: var(--green-400); }
        .card { background: var(--bg-card); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 24px; backdrop-filter: blur(16px); margin-bottom: 24px; }
        .filter-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 6px; }
        .form-group label { font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; }
        .form-control { background: rgba(0,0,0,0.3); border: 1px solid var(--border); color: var(--text-primary); padding: 10px 14px; border-radius: var(--radius-md); font-family: inherit; font-size: 0.85rem; outline: none; transition: border-color 0.2s; color-scheme: dark;}
        .form-control:focus { border-color: var(--green-400); }
        .btn-submit { background: linear-gradient(135deg, var(--green-600), var(--green-500)); color: white; border: none; padding: 12px 20px; border-radius: var(--radius-md); font-weight: 600; cursor: pointer; width: 100%; margin-top: 14px; }
        .data-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        .data-table th { text-align: left; padding: 12px 10px; font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); border-bottom: 1px solid var(--border); }
        .data-table td { padding: 14px 10px; border-bottom: 1px solid rgba(34,197,94,0.05); }
        .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 100px; font-size: 0.75rem; font-weight: 600; }
        .badge-green { background: rgba(34,197,94,0.12); color: var(--green-400); border: 1px solid rgba(34,197,94,0.2); }
        .badge-red { background: rgba(248,113,113,0.1); color: var(--red-400); border: 1px solid rgba(248,113,113,0.2); }
        .badge-blue { background: rgba(96,165,250,0.1); color: var(--blue-400); border: 1px solid rgba(96,165,250,0.2); }
        .badge-amber { background: rgba(251,191,36,0.1); color: var(--amber-400); border: 1px solid rgba(251,191,36,0.2); }
        .pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 20px; font-size: 0.85rem; color: var(--text-muted); }
        .pagination-buttons { display: flex; gap: 10px; }
        .btn-page { background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: var(--text-primary); padding: 8px 16px; border-radius: 8px; cursor: pointer; transition: 0.2s; }
        .btn-page:hover:not(:disabled) { background: rgba(34,197,94,0.15); border-color: var(--green-400); }
        .btn-page:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
</head>
<body>
<div class="app-wrapper">
    <div class="header">
        <div>
            <h1 style="font-size:1.5rem; font-weight:700;">Riwayat Data Sensor</h1>
            <p style="color:var(--text-muted); font-size:0.85rem; margin-top:4px;">Lihat dan filter seluruh riwayat log dari ESP32</p>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-back">⬅ Kembali</a>
    </div>

    <div class="card">
        <form id="filterForm">
            <div class="filter-grid">
                <div class="form-group">
                    <label>Mulai Tanggal</label>
                    <input type="date" class="form-control" name="start_date" id="startDate">
                </div>
                <div class="form-group">
                    <label>Sampai Tanggal</label>
                    <input type="date" class="form-control" name="end_date" id="endDate">
                </div>
                <div class="form-group">
                    <label>Status Hujan</label>
                    <select class="form-control" name="is_raining" id="isRaining">
                        <option value="">Semua</option>
                        <option value="true">Hujan</option>
                        <option value="false">Tidak Hujan</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Status Pompa</label>
                    <select class="form-control" name="pump_status" id="pumpStatus">
                        <option value="">Semua</option>
                        <option value="true">ON</option>
                        <option value="false">OFF</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn-submit">Terapkan Filter</button>
            <button type="button" class="btn-submit" id="btnReset" style="background:transparent; border:1px solid var(--border); margin-top:8px; display:none;" onclick="resetFilter()">Reset Filter</button>
        </form>
    </div>

    <div class="card">
        <div style="overflow-x:auto;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Waktu (RTC)</th>
                        <th>Kelembapan Tanah</th>
                        <th>Status Hujan</th>
                        <th>Status Pompa</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                    <tr><td colspan="4" style="text-align:center; color:var(--text-muted);">Memuat data...</td></tr>
                </tbody>
            </table>
        </div>
        <div class="pagination">
            <span id="pageInfo">Menampilkan data...</span>
            <div class="pagination-buttons">
                <button class="btn-page" id="btnPrev" onclick="changePage(-1)">Mundur</button>
                <button class="btn-page" id="btnNext" onclick="changePage(1)">Maju</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentPage = 1;
    let lastPage = 1;

    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        currentPage = 1;
        fetchData();
    });

    function toggleResetButton() {
        const sd = document.getElementById('startDate').value;
        const ed = document.getElementById('endDate').value;
        const rain = document.getElementById('isRaining').value;
        const pump = document.getElementById('pumpStatus').value;
        
        const isModified = sd !== "" || ed !== "" || rain !== "" || pump !== "";
        document.getElementById('btnReset').style.display = isModified ? 'block' : 'none';
    }

    // Pantau perubahan pada form filter
    ['startDate', 'endDate', 'isRaining', 'pumpStatus'].forEach(id => {
        document.getElementById(id).addEventListener('change', toggleResetButton);
        document.getElementById(id).addEventListener('input', toggleResetButton);
    });

    function resetFilter() {
        document.getElementById('filterForm').reset();
        toggleResetButton(); // Sembunyikan lagi tombolnya
        currentPage = 1;
        fetchData();
    }

    function changePage(step) {
        if (currentPage + step >= 1 && currentPage + step <= lastPage) {
            currentPage += step;
            fetchData();
        }
    }

    async function fetchData() {
        const tbody = document.getElementById('tableBody');
        tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:var(--text-muted);">Memuat data...</td></tr>';
        
        const params = new URLSearchParams();
        params.append('page', currentPage);
        
        const sd = document.getElementById('startDate').value;
        const ed = document.getElementById('endDate').value;
        const rain = document.getElementById('isRaining').value;
        const pump = document.getElementById('pumpStatus').value;

        if (sd) params.append('start_date', sd);
        if (ed) params.append('end_date', ed);
        if (rain !== "") params.append('is_raining', rain);
        if (pump !== "") params.append('pump_status', pump);

        try {
            const res = await fetch('/api/sensor/history?' + params.toString());
            const json = await res.json();
            
            const data = json.data;
            const meta = json.meta;
            
            if (!data || data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:var(--text-muted);">Data tidak ditemukan.</td></tr>';
                document.getElementById('pageInfo').textContent = "Tidak ada data";
                document.getElementById('btnPrev').disabled = true;
                document.getElementById('btnNext').disabled = true;
                return;
            }

            tbody.innerHTML = data.map(row => {
                const dt = new Date(row.recorded_at);
                const timeStr = dt.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'medium' });
                
                const moisture = row.soil_moisture ?? 0;
                const moistureColor = moisture < 30 ? 'var(--red-400)' : moisture < 60 ? 'var(--amber-400)' : 'var(--green-400)';
                
                const rainBadge = row.is_raining
                    ? '<span class="badge badge-blue">🌧 Hujan</span>'
                    : '<span class="badge badge-amber">☀️ Kering</span>';
                
                const pumpBadge = row.pump_status
                    ? '<span class="badge badge-green">💧 ON</span>'
                    : '<span class="badge badge-red">🚫 OFF</span>';

                return `<tr>
                    <td style="font-variant-numeric:tabular-nums;">${timeStr}</td>
                    <td><span style="color:${moistureColor}; font-weight:700;">${moisture}%</span> <span style="color:var(--text-muted);font-size:0.75rem;">(ADC: ${row.soil_adc})</span></td>
                    <td>${rainBadge}</td>
                    <td>${pumpBadge}</td>
                </tr>`;
            }).join('');

            // Pagination Handling
            lastPage = meta.last_page || 1;
            currentPage = meta.current_page || 1;
            
            if (meta) {
                document.getElementById('pageInfo').textContent = `Menampilkan ${meta.from || 0} - ${meta.to || 0} dari total ${meta.total || 0} data`;
            }
            document.getElementById('btnPrev').disabled = (currentPage === 1);
            document.getElementById('btnNext').disabled = (currentPage === lastPage);

        } catch (err) {
            tbody.innerHTML = '<tr><td colspan="4" style="text-align:center; color:var(--red-400);">Gagal memuat data dari server.</td></tr>';
            console.error(err);
        }
    }

    document.addEventListener('DOMContentLoaded', fetchData);
</script>
</body>
</html>
