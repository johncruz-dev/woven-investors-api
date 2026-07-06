<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Investors Dashboard</title>
    <style>
        :root {
            --bg: #f4f6f9;
            --card: #ffffff;
            --text: #1a2332;
            --muted: #64748b;
            --border: #e2e8f0;
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --success: #059669;
            --danger: #dc2626;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: system-ui, -apple-system, sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.5;
            min-height: 100vh;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem 1.25rem 3rem;
        }

        header {
            margin-bottom: 2rem;
        }

        header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        header p {
            color: var(--muted);
        }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
        }

        .card h2 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .upload-row {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }

        input[type="file"] {
            flex: 1;
            min-width: 200px;
            padding: 0.5rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover:not(:disabled) {
            background: var(--primary-hover);
        }

        .btn-secondary {
            background: #fff;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .btn-secondary:hover:not(:disabled) {
            background: var(--bg);
        }

        .metrics {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .metric-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 12px;
            padding: 1.25rem;
        }

        .metric-card .label {
            font-size: 0.8125rem;
            color: var(--muted);
            margin-bottom: 0.25rem;
        }

        .metric-card .value {
            font-size: 1.5rem;
            font-weight: 700;
        }

        .alert {
            padding: 0.75rem 1rem;
            border-radius: 8px;
            font-size: 0.875rem;
            margin-bottom: 1rem;
            display: none;
        }

        .alert.show { display: block; }

        .alert-success {
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fef2f2;
            color: #991b1b;
            border: 1px solid #fecaca;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
        }

        th, td {
            text-align: left;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid var(--border);
        }

        th {
            font-weight: 600;
            color: var(--muted);
            background: #f8fafc;
        }

        tr:last-child td { border-bottom: none; }

        .amount {
            font-variant-numeric: tabular-nums;
            font-weight: 500;
        }

        .table-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border);
        }

        .pagination-summary {
            font-size: 0.875rem;
            color: var(--muted);
        }

        .pagination-summary strong {
            color: var(--text);
            font-weight: 600;
        }

        .pagination-nav {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }

        .page-btn {
            min-width: 2.25rem;
            height: 2.25rem;
            padding: 0 0.5rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            color: var(--text);
            font-size: 0.8125rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.15s;
        }

        .page-btn:hover:not(:disabled):not(.active):not(.ellipsis) {
            background: var(--bg);
            border-color: #cbd5e1;
        }

        .page-btn:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .page-btn.active {
            background: var(--primary);
            border-color: var(--primary);
            color: #fff;
        }

        .page-btn.ellipsis {
            border: none;
            background: transparent;
            cursor: default;
            min-width: 1.5rem;
        }

        .page-btn.nav-btn {
            padding: 0 0.625rem;
        }

        .per-page-wrap {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8125rem;
            color: var(--muted);
        }

        .per-page-wrap select {
            padding: 0.375rem 0.625rem;
            border: 1px solid var(--border);
            border-radius: 8px;
            background: #fff;
            font-size: 0.8125rem;
            color: var(--text);
            cursor: pointer;
        }

        .table-footer {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            margin-top: 1.25rem;
            padding-top: 1.25rem;
            border-top: 1px solid var(--border);
        }

        .empty-state, .loading {
            text-align: center;
            padding: 2rem;
            color: var(--muted);
            font-size: 0.875rem;
        }

        .spinner {
            display: inline-block;
            width: 1rem;
            height: 1rem;
            border: 2px solid var(--border);
            border-top-color: var(--primary);
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        .hidden { display: none !important; }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Investors Dashboard</h1>
            <p>Import CSV data and view investor metrics</p>
        </header>

        <div id="alert" class="alert" role="alert"></div>

        <section class="card">
            <h2>Import CSV</h2>
            <form id="import-form" class="upload-row">
                <input type="file" id="csv-file" name="file" accept=".csv,.txt" required>
                <button type="submit" class="btn btn-primary" id="import-btn">Upload & Import</button>
            </form>
        </section>

        <section id="results-section" class="hidden">
        <section class="metrics" id="metrics">
            <div class="metric-card">
                <div class="label">Average Age</div>
                <div class="value" id="metric-age">—</div>
            </div>
            <div class="metric-card">
                <div class="label">Avg Investment Amount</div>
                <div class="value" id="metric-amount">—</div>
            </div>
            <div class="metric-card">
                <div class="label">Total Investments</div>
                <div class="value" id="metric-total">—</div>
            </div>
        </section>

        <section class="card">
            <div class="table-header">
                <h2>Investors</h2>
                <div style="display:flex;gap:0.5rem;">
                    <a href="/api/v1/investors?format=csv" class="btn btn-secondary hidden" id="export-btn" download>Export CSV</a>
                </div>
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Age</th>
                            <th>Investment Amount</th>
                        </tr>
                    </thead>
                    <tbody id="investors-body">
                        <tr>
                            <td colspan="4" class="empty-state">No data yet. Import a CSV file above to see results.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="table-footer" id="pagination" style="display:none;">
                <div class="pagination-summary" id="pagination-summary"></div>

                <div class="per-page-wrap">
                    <label for="per-page-select">Rows per page</label>
                    <select id="per-page-select">
                        <option value="10" selected>10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                    </select>
                </div>

                <nav class="pagination-nav" id="pagination-nav" aria-label="Investor pagination"></nav>
            </div>
        </section>
        </section>
    </div>

    <script>
        const API = '/api/v1';
        let currentPage = 1;
        let hasData = false;
        let perPage = 10;
        let lastMeta = null;

        const alertEl = document.getElementById('alert');
        const importForm = document.getElementById('import-form');
        const importBtn = document.getElementById('import-btn');
        const resultsSection = document.getElementById('results-section');
        const exportBtn = document.getElementById('export-btn');
        const investorsBody = document.getElementById('investors-body');
        const paginationEl = document.getElementById('pagination');
        const paginationSummary = document.getElementById('pagination-summary');
        const paginationNav = document.getElementById('pagination-nav');
        const perPageSelect = document.getElementById('per-page-select');

        function showAlert(message, type = 'success') {
            alertEl.textContent = message;
            alertEl.className = `alert alert-${type} show`;
            setTimeout(() => alertEl.classList.remove('show'), 5000);
        }

        function formatCurrency(value) {
            return new Intl.NumberFormat('en-GB', {
                style: 'currency',
                currency: 'GBP',
                minimumFractionDigits: 2,
            }).format(value);
        }

        async function fetchJson(url, options = {}) {
            const response = await fetch(url, {
                headers: { 'Accept': 'application/json' },
                ...options,
            });

            const data = await response.json().catch(() => ({}));

            if (!response.ok) {
                const message = data.message
                    || (data.errors ? Object.values(data.errors).flat().join(' ') : null)
                    || 'Request failed';
                throw new Error(message);
            }

            return data;
        }

        async function loadMetrics() {
            const [age, amount, total] = await Promise.all([
                fetchJson(`${API}/metrics/average-age`),
                fetchJson(`${API}/metrics/average-investment-amount`),
                fetchJson(`${API}/metrics/total-investments`),
            ]);

            document.getElementById('metric-age').textContent = age.average_age ?? '—';
            document.getElementById('metric-amount').textContent =
                amount.average_investment_amount != null
                    ? formatCurrency(amount.average_investment_amount)
                    : '—';
            document.getElementById('metric-total').textContent = total.total_investments ?? '0';
        }

        function getPageNumbers(current, last) {
            if (last <= 7) {
                return Array.from({ length: last }, (_, i) => i + 1);
            }

            const pages = [1];
            const start = Math.max(2, current - 1);
            const end = Math.min(last - 1, current + 1);

            if (start > 2) {
                pages.push('...');
            }

            for (let i = start; i <= end; i++) {
                pages.push(i);
            }

            if (end < last - 1) {
                pages.push('...');
            }

            pages.push(last);

            return pages;
        }

        function renderPagination(meta) {
            lastMeta = meta;
            const from = ((meta.current_page - 1) * meta.per_page) + 1;
            const to = Math.min(meta.current_page * meta.per_page, meta.total);

            paginationSummary.innerHTML = meta.total === 0
                ? 'No results'
                : `Showing <strong>${from}</strong>–<strong>${to}</strong> of <strong>${meta.total}</strong> investors`;

            const pages = getPageNumbers(meta.current_page, meta.last_page);

            paginationNav.innerHTML = `
                <button type="button" class="page-btn nav-btn" data-page="1" ${meta.current_page <= 1 ? 'disabled' : ''} aria-label="First page">«</button>
                <button type="button" class="page-btn nav-btn" data-page="${meta.current_page - 1}" ${meta.current_page <= 1 ? 'disabled' : ''} aria-label="Previous page">‹</button>
                ${pages.map(page => {
                    if (page === '...') {
                        return '<span class="page-btn ellipsis">…</span>';
                    }
                    const isActive = page === meta.current_page;
                    return `<button type="button" class="page-btn${isActive ? ' active' : ''}" data-page="${page}" ${isActive ? 'aria-current="page"' : ''}>${page}</button>`;
                }).join('')}
                <button type="button" class="page-btn nav-btn" data-page="${meta.current_page + 1}" ${meta.current_page >= meta.last_page ? 'disabled' : ''} aria-label="Next page">›</button>
                <button type="button" class="page-btn nav-btn" data-page="${meta.last_page}" ${meta.current_page >= meta.last_page ? 'disabled' : ''} aria-label="Last page">»</button>
            `;

            paginationNav.querySelectorAll('[data-page]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const page = parseInt(btn.dataset.page, 10);
                    if (!Number.isNaN(page) && page !== meta.current_page) {
                        loadInvestors(page);
                    }
                });
            });
        }

        async function loadInvestors(page = 1) {
            investorsBody.innerHTML = '<tr><td colspan="4" class="loading"><span class="spinner"></span> Loading…</td></tr>';

            const data = await fetchJson(`${API}/investors?per_page=${perPage}&page=${page}`);
            currentPage = data.meta.current_page;

            if (!data.data.length) {
                investorsBody.innerHTML = '<tr><td colspan="4" class="empty-state">No investors yet. Import a CSV file to get started.</td></tr>';
                paginationEl.style.display = 'none';
                return;
            }

            investorsBody.innerHTML = data.data.map(investor => `
                <tr>
                    <td>${investor.investor_id}</td>
                    <td>${investor.name}</td>
                    <td>${investor.age}</td>
                    <td class="amount">${formatCurrency(investor.investment_amount)}</td>
                </tr>
            `).join('');

            paginationEl.style.display = 'flex';
            renderPagination(data.meta);
        }

        async function refreshAll() {
            if (!hasData) return;
            await Promise.all([loadMetrics(), loadInvestors(currentPage)]);
        }

        function showResults() {
            hasData = true;
            resultsSection.classList.remove('hidden');
            exportBtn.classList.remove('hidden');
        }

        importForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const fileInput = document.getElementById('csv-file');
            if (!fileInput.files.length) return;

            const formData = new FormData();
            formData.append('file', fileInput.files[0]);

            importBtn.disabled = true;
            importBtn.innerHTML = '<span class="spinner"></span> Importing…';

            try {
                const data = await fetchJson(`${API}/import`, {
                    method: 'POST',
                    body: formData,
                });

                showAlert(
                    `${data.message} (${data.data.investors_upserted} investors, ${data.data.investments_upserted} investments)`
                );

                fileInput.value = '';
                currentPage = 1;
                showResults();
                await refreshAll();
            } catch (error) {
                showAlert(error.message, 'error');
            } finally {
                importBtn.disabled = false;
                importBtn.textContent = 'Upload & Import';
            }
        });

        perPageSelect.addEventListener('change', () => {
            perPage = parseInt(perPageSelect.value, 10);
            if (hasData) {
                currentPage = 1;
                loadInvestors(1);
            }
        });

    </script>
</body>
</html>
