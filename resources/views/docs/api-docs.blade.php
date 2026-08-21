<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Waldo API Documentation &amp; Testing Console</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Swagger UI CSS & JS for alternate native view -->
    <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui.css" />

    <style>
        :root {
            --bg-primary: #0b0f19;
            --bg-secondary: #111827;
            --bg-card: #1f2937;
            --bg-card-hover: #283548;
            --border-color: #374151;
            --border-light: rgba(255, 255, 255, 0.08);
            --text-main: #f3f4f6;
            --text-muted: #9ca3af;
            --accent-blue: #3b82f6;
            --accent-blue-hover: #2563eb;
            --accent-green: #10b981;
            --accent-purple: #8b5cf6;
            --accent-amber: #f59e0b;
            --accent-rose: #f43f5e;
            --code-bg: #0d1117;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-main);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        header {
            background: rgba(17, 24, 39, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 50;
            padding: 0.875rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .brand-container {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .brand-logo {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-weight: 800;
            font-size: 1.25rem;
            box-shadow: 0 4px 14px rgba(59, 130, 246, 0.35);
        }

        .brand-title {
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            color: #ffffff;
        }

        .brand-badge {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.2rem 0.55rem;
            border-radius: 6px;
            text-transform: uppercase;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .btn {
            font-family: inherit;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0.5rem 0.95rem;
            border-radius: 8px;
            border: 1px solid transparent;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 0.45rem;
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-outline {
            background: transparent;
            border-color: var(--border-color);
            color: var(--text-main);
        }

        .btn-outline:hover {
            background: var(--bg-card);
            border-color: var(--text-muted);
        }

        .btn-primary {
            background: var(--accent-blue);
            color: #ffffff;
        }

        .btn-primary:hover {
            background: var(--accent-blue-hover);
        }

        /* Layout */
        .layout-wrapper {
            display: grid;
            grid-template-columns: 280px 1fr;
            flex: 1;
            min-height: calc(100vh - 65px);
        }

        /* Sidebar */
        aside {
            background: var(--bg-secondary);
            border-right: 1px solid var(--border-color);
            padding: 1.25rem 1rem;
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            position: sticky;
            top: 65px;
            height: calc(100vh - 65px);
            overflow-y: auto;
        }

        .sidebar-section-title {
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            margin-bottom: 0.5rem;
            padding-left: 0.5rem;
        }

        .nav-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .nav-item-btn {
            display: flex;
            align-items: center;
            justify-content: space-between;
            width: 100%;
            padding: 0.6rem 0.75rem;
            border-radius: 8px;
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 500;
            text-decoration: none;
            background: transparent;
            border: none;
            cursor: pointer;
            text-align: left;
            transition: all 0.15s ease;
        }

        .nav-item-btn:hover {
            background: var(--bg-card);
            color: #ffffff;
        }

        .nav-item-btn.active {
            background: rgba(59, 130, 246, 0.15);
            color: #93c5fd;
            font-weight: 600;
            border: 1px solid rgba(59, 130, 246, 0.25);
        }

        .method-badge {
            font-size: 0.65rem;
            font-weight: 800;
            padding: 0.15rem 0.4rem;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            text-transform: uppercase;
        }

        .method-get {
            background: rgba(16, 185, 129, 0.2);
            color: #34d399;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        /* Main Content */
        main {
            padding: 2rem 2.5rem;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
        }

        .tab-nav {
            display: flex;
            gap: 0.5rem;
            border-bottom: 1px solid var(--border-color);
            margin-bottom: 2rem;
            padding-bottom: 0.25rem;
        }

        .tab-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            cursor: pointer;
            border-radius: 8px 8px 0 0;
            position: relative;
            transition: all 0.2s;
        }

        .tab-btn:hover {
            color: #ffffff;
        }

        .tab-btn.active {
            color: #60a5fa;
        }

        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -0.25rem;
            left: 0;
            right: 0;
            height: 2px;
            background: #3b82f6;
            border-radius: 2px;
        }

        /* Endpoint Card */
        .endpoint-card {
            background: var(--bg-secondary);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            margin-bottom: 2rem;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3);
            transition: border-color 0.2s;
        }

        .endpoint-card:hover {
            border-color: #4b5563;
        }

        .endpoint-header {
            padding: 1.25rem 1.5rem;
            background: var(--bg-card);
            border-bottom: 1px solid var(--border-color);
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .endpoint-identity {
            display: flex;
            align-items: center;
            gap: 0.85rem;
        }

        .endpoint-path {
            font-family: 'JetBrains Mono', monospace;
            font-size: 1.05rem;
            font-weight: 600;
            color: #ffffff;
        }

        .endpoint-summary {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .endpoint-body {
            padding: 1.5rem;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.75rem;
        }

        @media (max-width: 1024px) {
            .endpoint-body {
                grid-template-columns: 1fr;
            }
            .layout-wrapper {
                grid-template-columns: 1fr;
            }
            aside {
                display: none;
            }
        }

        .form-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #e5e7eb;
            margin-bottom: 0.85rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .param-group {
            display: flex;
            flex-direction: column;
            gap: 0.85rem;
            margin-bottom: 1.25rem;
        }

        .param-field {
            display: flex;
            flex-direction: column;
            gap: 0.35rem;
        }

        .param-field label {
            font-size: 0.8rem;
            font-weight: 600;
            color: #d1d5db;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .param-type {
            font-size: 0.7rem;
            color: var(--text-muted);
            font-family: 'JetBrains Mono', monospace;
        }

        .param-input, .param-select {
            background: var(--code-bg);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            color: #ffffff;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.85rem;
            padding: 0.55rem 0.75rem;
            outline: none;
            transition: border-color 0.2s;
        }

        .param-input:focus, .param-select:focus {
            border-color: var(--accent-blue);
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
        }

        .param-checkbox-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            background: var(--code-bg);
            padding: 0.75rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            color: #e5e7eb;
            cursor: pointer;
        }

        .checkbox-item input {
            cursor: pointer;
            accent-color: var(--accent-blue);
        }

        /* Console Output */
        .console-panel {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .console-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .status-badge {
            font-size: 0.75rem;
            font-weight: 700;
            padding: 0.2rem 0.5rem;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
        }

        .status-200 { background: rgba(16, 185, 129, 0.2); color: #34d399; }
        .status-404 { background: rgba(244, 63, 94, 0.2); color: #fb7185; }
        .status-500 { background: rgba(239, 68, 68, 0.2); color: #f87171; }

        .time-badge {
            font-size: 0.75rem;
            color: var(--text-muted);
            font-family: 'JetBrains Mono', monospace;
        }

        .code-output {
            background: var(--code-bg);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 1rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.8rem;
            color: #93c5fd;
            overflow-x: auto;
            max-height: 480px;
            white-space: pre-wrap;
            word-break: break-all;
            line-height: 1.5;
        }

        .raw-curl-box {
            background: #090d14;
            border: 1px solid #1f2937;
            border-radius: 6px;
            padding: 0.6rem 0.75rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.75rem;
            color: #a78bfa;
            word-break: break-all;
            margin-top: 0.5rem;
        }

        /* Swagger container */
        #swagger-ui-container {
            background: #ffffff;
            border-radius: 12px;
            padding: 1.5rem;
            display: none;
        }

        .swagger-ui .topbar { display: none !important; }
    </style>
</head>
<body>

    <header>
        <div class="brand-container">
            <div class="brand-logo"><i class="fa-solid fa-cube"></i></div>
            <div>
                <div class="brand-title">Waldo REST API</div>
                <div style="font-size: 0.75rem; color: var(--text-muted);">Interactive Documentation &amp; Live Test Console</div>
            </div>
            <span class="brand-badge">v1.0.0</span>
        </div>

        <div class="header-actions">
            <a href="{{ route('api.docs.spec') }}" target="_blank" class="btn btn-outline">
                <i class="fa-solid fa-file-code"></i> OpenAPI JSON
            </a>
            <a href="{{ url('/') }}" class="btn btn-outline">
                <i class="fa-solid fa-arrow-left"></i> Back to App
            </a>
        </div>
    </header>

    <div class="layout-wrapper">
        <!-- Sidebar -->
        <aside>
            <div>
                <div class="sidebar-section-title">Employees API</div>
                <ul class="nav-list">
                    <li>
                        <button class="nav-item-btn active" onclick="scrollToEndpoint('endpoint-list-employees')">
                            <span>Get All / Search</span>
                            <span class="method-badge method-get">GET</span>
                        </button>
                    </li>
                    <li>
                        <button class="nav-item-btn" onclick="scrollToEndpoint('endpoint-get-employee')">
                            <span>Get By Code</span>
                            <span class="method-badge method-get">GET</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div>
                <div class="sidebar-section-title">Typography / Fonts API</div>
                <ul class="nav-list">
                    <li>
                        <button class="nav-item-btn" onclick="scrollToEndpoint('endpoint-list-fonts')">
                            <span>List Fonts</span>
                            <span class="method-badge method-get">GET</span>
                        </button>
                    </li>
                </ul>
            </div>

            <div style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--border-color);">
                <div style="font-size: 0.75rem; color: var(--text-muted); line-height: 1.4;">
                    Base URL: <br><strong style="color: #ffffff; font-family: 'JetBrains Mono', monospace;">{{ $baseUrl }}</strong>
                </div>
            </div>
        </aside>

        <!-- Main Workspace -->
        <main>
            <div class="tab-nav">
                <button class="tab-btn active" id="tab-custom-btn" onclick="switchView('custom')">
                    <i class="fa-solid fa-terminal"></i> Interactive Console
                </button>
                <button class="tab-btn" id="tab-swagger-btn" onclick="switchView('swagger')">
                    <i class="fa-solid fa-book-open"></i> Swagger Explorer
                </button>
            </div>

            <div id="custom-console-container">
                <!-- 1. GET /api/v1/employees -->
                <div class="endpoint-card" id="endpoint-list-employees">
                    <div class="endpoint-header">
                        <div class="endpoint-identity">
                            <span class="method-badge method-get">GET</span>
                            <span class="endpoint-path">/api/v1/employees</span>
                        </div>
                        <div class="endpoint-summary">List, search, filter, sort &amp; paginate employees</div>
                    </div>

                    <div class="endpoint-body">
                        <!-- Request form -->
                        <div>
                            <div class="form-section-title"><i class="fa-solid fa-sliders"></i> Query Parameters</div>
                            
                            <div class="param-group">
                                <div class="param-field">
                                    <label>Search Keyword <span class="param-type">string</span></label>
                                    <input type="text" id="filter-search" class="param-input" placeholder="e.g. John, 1001, phone, SSID">
                                </div>

                                <div class="param-field">
                                    <label>Status <span class="param-type">string</span></label>
                                    <select id="filter-status" class="param-select">
                                        <option value="">-- All Statuses --</option>
                                        <option value="Active">Active</option>
                                        <option value="Resigned">Resigned</option>
                                        <option value="Terminated">Terminated</option>
                                        <option value="On Leave">On Leave</option>
                                    </select>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                    <div class="param-field">
                                        <label>Sort By <span class="param-type">string</span></label>
                                        <select id="filter-sort-by" class="param-select">
                                            <option value="employee_code">employee_code</option>
                                            <option value="name">name</option>
                                            <option value="join_date_formatted">join_date_formatted</option>
                                            <option value="employee_status">employee_status</option>
                                            <option value="created_at">created_at</option>
                                        </select>
                                    </div>
                                    <div class="param-field">
                                        <label>Sort Order <span class="param-type">string</span></label>
                                        <select id="filter-sort-order" class="param-select">
                                            <option value="asc">ASC</option>
                                            <option value="desc">DESC</option>
                                        </select>
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                                    <div class="param-field">
                                        <label>Per Page (1-100) <span class="param-type">int</span></label>
                                        <input type="number" id="filter-per-page" class="param-input" value="10" min="1" max="100">
                                    </div>
                                    <div class="param-field">
                                        <label>Page <span class="param-type">int</span></label>
                                        <input type="number" id="filter-page" class="param-input" value="1" min="1">
                                    </div>
                                </div>
                            </div>

                            <div class="form-section-title"><i class="fa-solid fa-link"></i> Include Relationships</div>
                            <div class="param-checkbox-group">
                                <label class="checkbox-item"><input type="checkbox" id="inc-suspensions"> Suspensions</label>
                                <label class="checkbox-item"><input type="checkbox" id="inc-leaver"> Leaver Info</label>
                                <label class="checkbox-item"><input type="checkbox" id="inc-termination"> Termination</label>
                                <label class="checkbox-item"><input type="checkbox" id="inc-adjustments"> Adjustments</label>
                                <label class="checkbox-item" style="grid-column: span 2;"><input type="checkbox" id="inc-all"> Include All Relations</label>
                            </div>

                            <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
                                <button class="btn btn-primary" onclick="testListEmployees()">
                                    <i class="fa-solid fa-play"></i> Send Request
                                </button>
                                <button class="btn btn-outline" onclick="copyCurl('curl-box-list')">
                                    <i class="fa-regular fa-copy"></i> Copy cURL
                                </button>
                            </div>

                            <div class="raw-curl-box" id="curl-box-list">
                                curl -X GET "{{ $baseUrl }}/api/v1/employees" -H "Accept: application/json"
                            </div>
                        </div>

                        <!-- Response Console -->
                        <div class="console-panel">
                            <div class="console-header">
                                <div class="form-section-title" style="margin-bottom: 0;"><i class="fa-solid fa-code"></i> Live Response</div>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <span id="badge-list-status" class="status-badge" style="display: none;"></span>
                                    <span id="badge-list-time" class="time-badge"></span>
                                </div>
                            </div>
                            <pre class="code-output" id="output-list-employees">// Click "Send Request" to execute query...</pre>
                        </div>
                    </div>
                </div>

                <!-- 2. GET /api/v1/employees/{employeeCode} -->
                <div class="endpoint-card" id="endpoint-get-employee">
                    <div class="endpoint-header">
                        <div class="endpoint-identity">
                            <span class="method-badge method-get">GET</span>
                            <span class="endpoint-path">/api/v1/employees/{employeeCode}</span>
                        </div>
                        <div class="endpoint-summary">Get comprehensive employee profile and details</div>
                    </div>

                    <div class="endpoint-body">
                        <!-- Request form -->
                        <div>
                            <div class="form-section-title"><i class="fa-solid fa-key"></i> Path Parameters</div>
                            <div class="param-group">
                                <div class="param-field">
                                    <label>Employee Code (Required) <span class="param-type">string</span></label>
                                    <input type="text" id="param-emp-code" class="param-input" placeholder="e.g. 1001 or EMP001">
                                </div>
                            </div>

                            <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
                                <button class="btn btn-primary" onclick="testGetEmployee()">
                                    <i class="fa-solid fa-play"></i> Send Request
                                </button>
                                <button class="btn btn-outline" onclick="copyCurl('curl-box-get')">
                                    <i class="fa-regular fa-copy"></i> Copy cURL
                                </button>
                            </div>

                            <div class="raw-curl-box" id="curl-box-get">
                                curl -X GET "{{ $baseUrl }}/api/v1/employees/1001" -H "Accept: application/json"
                            </div>
                        </div>

                        <!-- Response Console -->
                        <div class="console-panel">
                            <div class="console-header">
                                <div class="form-section-title" style="margin-bottom: 0;"><i class="fa-solid fa-code"></i> Live Response</div>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <span id="badge-get-status" class="status-badge" style="display: none;"></span>
                                    <span id="badge-get-time" class="time-badge"></span>
                                </div>
                            </div>
                            <pre class="code-output" id="output-get-employee">// Click "Send Request" to test endpoint...</pre>
                        </div>
                    </div>
                </div>

                <!-- 3. GET /letters/fonts/api -->
                <div class="endpoint-card" id="endpoint-list-fonts">
                    <div class="endpoint-header">
                        <div class="endpoint-identity">
                            <span class="method-badge method-get">GET</span>
                            <span class="endpoint-path">/letters/fonts/api</span>
                        </div>
                        <div class="endpoint-summary">Get registered typography font assets</div>
                    </div>

                    <div class="endpoint-body">
                        <div>
                            <p style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 1.25rem;">
                                Retrieves list of font assets configured for letter and certificate rendering.
                            </p>

                            <div style="display: flex; gap: 0.75rem;">
                                <button class="btn btn-primary" onclick="testListFonts()">
                                    <i class="fa-solid fa-play"></i> Send Request
                                </button>
                                <button class="btn btn-outline" onclick="copyCurl('curl-box-fonts')">
                                    <i class="fa-regular fa-copy"></i> Copy cURL
                                </button>
                            </div>

                            <div class="raw-curl-box" id="curl-box-fonts">
                                curl -X GET "{{ $baseUrl }}/letters/fonts/api" -H "Accept: application/json"
                            </div>
                        </div>

                        <div class="console-panel">
                            <div class="console-header">
                                <div class="form-section-title" style="margin-bottom: 0;"><i class="fa-solid fa-code"></i> Live Response</div>
                                <div style="display: flex; gap: 0.5rem; align-items: center;">
                                    <span id="badge-fonts-status" class="status-badge" style="display: none;"></span>
                                    <span id="badge-fonts-time" class="time-badge"></span>
                                </div>
                            </div>
                            <pre class="code-output" id="output-list-fonts">// Click "Send Request" to test endpoint...</pre>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Swagger UI Container -->
            <div id="swagger-ui-container">
                <div id="swagger-ui"></div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://unpkg.com/swagger-ui-dist@5.11.0/swagger-ui-bundle.js"></script>
    <script>
        const baseUrl = "{{ $baseUrl }}";
        const specUrl = "{{ $specUrl }}";

        // Navigation scroll
        function scrollToEndpoint(id) {
            document.getElementById(id)?.scrollIntoView({ behavior: 'smooth' });
            document.querySelectorAll('.nav-item-btn').forEach(b => b.classList.remove('active'));
            event.currentTarget.classList.add('active');
        }

        // View toggle
        let swaggerInitialized = false;
        function switchView(view) {
            const customTab = document.getElementById('tab-custom-btn');
            const swaggerTab = document.getElementById('tab-swagger-btn');
            const customContainer = document.getElementById('custom-console-container');
            const swaggerContainer = document.getElementById('swagger-ui-container');

            if (view === 'swagger') {
                customTab.classList.remove('active');
                swaggerTab.classList.add('active');
                customContainer.style.display = 'none';
                swaggerContainer.style.display = 'block';

                if (!swaggerInitialized) {
                    SwaggerUIBundle({
                        url: specUrl,
                        dom_id: '#swagger-ui',
                        deepLinking: true,
                        presets: [
                            SwaggerUIBundle.presets.apis,
                            SwaggerUIBundle.SwaggerUIStandalonePreset
                        ],
                        layout: "BaseLayout"
                    });
                    swaggerInitialized = true;
                }
            } else {
                swaggerTab.classList.remove('active');
                customTab.classList.add('active');
                swaggerContainer.style.display = 'none';
                customContainer.style.display = 'block';
            }
        }

        // Helpers
        function copyCurl(elementId) {
            const text = document.getElementById(elementId).innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert('cURL command copied to clipboard!');
            });
        }

        function setStatusBadge(badgeId, timeId, status, duration) {
            const badge = document.getElementById(badgeId);
            const time = document.getElementById(timeId);
            badge.style.display = 'inline-block';
            badge.innerText = `HTTP ${status}`;
            badge.className = 'status-badge ' + (status >= 200 && status < 300 ? 'status-200' : (status === 404 ? 'status-404' : 'status-500'));
            time.innerText = `${duration}ms`;
        }

        // Test Endpoint 1: List Employees
        async function testListEmployees() {
            const params = new URLSearchParams();
            const search = document.getElementById('filter-search').value.trim();
            const status = document.getElementById('filter-status').value;
            const sortBy = document.getElementById('filter-sort-by').value;
            const sortOrder = document.getElementById('filter-sort-order').value;
            const perPage = document.getElementById('filter-per-page').value;
            const page = document.getElementById('filter-page').value;

            if (search) params.append('search', search);
            if (status) params.append('status', status);
            if (sortBy) params.append('sort_by', sortBy);
            if (sortOrder) params.append('sort_order', sortOrder);
            if (perPage) params.append('per_page', perPage);
            if (page) params.append('page', page);

            if (document.getElementById('inc-suspensions').checked) params.append('include_suspensions', '1');
            if (document.getElementById('inc-leaver').checked) params.append('include_leaver', '1');
            if (document.getElementById('inc-termination').checked) params.append('include_termination', '1');
            if (document.getElementById('inc-adjustments').checked) params.append('include_adjustments', '1');
            if (document.getElementById('inc-all').checked) params.append('include_all', '1');

            const queryString = params.toString() ? `?${params.toString()}` : '';
            const url = `${baseUrl}/api/v1/employees${queryString}`;
            
            document.getElementById('curl-box-list').innerText = `curl -X GET "${url}" -H "Accept: application/json"`;
            const output = document.getElementById('output-list-employees');
            output.innerText = 'Sending request...';

            const startTime = performance.now();
            try {
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });
                const duration = Math.round(performance.now() - startTime);
                const data = await response.json();
                setStatusBadge('badge-list-status', 'badge-list-time', response.status, duration);
                output.innerText = JSON.stringify(data, null, 2);
            } catch (err) {
                const duration = Math.round(performance.now() - startTime);
                setStatusBadge('badge-list-status', 'badge-list-time', 500, duration);
                output.innerText = `Error: ${err.message}`;
            }
        }

        // Test Endpoint 2: Get Employee by code
        async function testGetEmployee() {
            let code = document.getElementById('param-emp-code').value.trim();
            if (!code) {
                code = '1001';
                document.getElementById('param-emp-code').value = '1001';
            }

            const url = `${baseUrl}/api/v1/employees/${encodeURIComponent(code)}`;
            document.getElementById('curl-box-get').innerText = `curl -X GET "${url}" -H "Accept: application/json"`;
            const output = document.getElementById('output-get-employee');
            output.innerText = 'Sending request...';

            const startTime = performance.now();
            try {
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });
                const duration = Math.round(performance.now() - startTime);
                const data = await response.json();
                setStatusBadge('badge-get-status', 'badge-get-time', response.status, duration);
                output.innerText = JSON.stringify(data, null, 2);
            } catch (err) {
                const duration = Math.round(performance.now() - startTime);
                setStatusBadge('badge-get-status', 'badge-get-time', 500, duration);
                output.innerText = `Error: ${err.message}`;
            }
        }

        // Test Endpoint 3: Fonts
        async function testListFonts() {
            const url = `${baseUrl}/letters/fonts/api`;
            document.getElementById('curl-box-fonts').innerText = `curl -X GET "${url}" -H "Accept: application/json"`;
            const output = document.getElementById('output-list-fonts');
            output.innerText = 'Sending request...';

            const startTime = performance.now();
            try {
                const response = await fetch(url, {
                    headers: { 'Accept': 'application/json' }
                });
                const duration = Math.round(performance.now() - startTime);
                const data = await response.json();
                setStatusBadge('badge-fonts-status', 'badge-fonts-time', response.status, duration);
                output.innerText = JSON.stringify(data, null, 2);
            } catch (err) {
                const duration = Math.round(performance.now() - startTime);
                setStatusBadge('badge-fonts-status', 'badge-fonts-time', 500, duration);
                output.innerText = `Error: ${err.message}`;
            }
        }
    </script>
</body>
</html>
