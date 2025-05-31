<?php
// Database configuration with improved security
$dbConfig = [
    'host' => 'localhost',
    'dbname' => 'erp_management',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4'  // Better support for emojis and special characters
];

// API Endpoint with better security and validation
if (isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    try {
        // Connect to database with improved error handling
        $pdo = new PDO(
            "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}",
            $dbConfig['username'],
            $dbConfig['password'],
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]
        );

        // Validate action parameter
        $action = $_GET['action'];
        if (!in_array($action, ['getHolidays'])) {
            throw new InvalidArgumentException('Invalid action');
        }

        switch ($action) {
            case 'getHolidays':
                $countryCode = isset($_GET['country']) ? strtoupper(trim($_GET['country'])) : null;
                $year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
                $month = isset($_GET['month']) ? trim($_GET['month']) : null;

                // Validate inputs more thoroughly
                if ($countryCode && !preg_match('/^[A-Z]{2}$/', $countryCode)) {
                    throw new InvalidArgumentException('Invalid country code');
                }
                if ($year < 2000 || $year > 2100) {
                    throw new InvalidArgumentException('Year must be between 2000-2100');
                }
                if ($month && !preg_match('/^(0[1-9]|1[0-2])$/', $month)) {
                    throw new InvalidArgumentException('Invalid month');
                }

                // Build parameterized query
                $sql = "SELECT 
                            id, 
                            country_code as countryCode,
                            holiday_date as date,
                            name,
                            local_name as localName,
                            type,
                            description
                        FROM holidays 
                        WHERE YEAR(holiday_date) = :year";
                $params = [':year' => $year];
                
                if ($countryCode) {
                    $sql .= " AND country_code = :countryCode";
                    $params[':countryCode'] = $countryCode;
                }
                if ($month) {
                    $sql .= " AND MONTH(holiday_date) = :month";
                    $params[':month'] = $month;
                }
                
                $sql .= " ORDER BY holiday_date ASC";
                
                // Execute query
                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);
                $holidays = $stmt->fetchAll();
                
                // Format dates consistently
                foreach ($holidays as &$holiday) {
                    $holiday['date'] = date('Y-m-d', strtotime($holiday['date']));
                }
                
                echo json_encode([
                    'success' => true,
                    'count' => count($holidays),
                    'holidays' => $holidays,
                    'generated_at' => date('Y-m-d H:i:s')
                ], JSON_UNESCAPED_UNICODE);  // Preserve Unicode characters
                exit;

            default:
                throw new Exception('Invalid action');
        }
    } catch (InvalidArgumentException $e) {
        http_response_code(400);  // Bad request
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        error_log('Holiday API Error: ' . $e->getMessage());  // Log the error
        echo json_encode([
            'success' => false,
            'message' => 'An error occurred while processing your request'
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Holiday Calendar</title>
  <meta name="description" content="Interactive holiday calendar with country and date filters">
  <style>
    :root {
      --bg-color: #f5f7fa;
      --text-color: #333;
      --border-color: #ddd;
      --card-bg: white;
      --card-shadow: 0 2px 5px rgba(0,0,0,0.1);
      --secondary-text: #666;
      --primary-accent: #1976d2;
      --primary-accent-light: #e3f2fd;
      --error-color: #d32f2f;
    }
    .dark-mode {
      --bg-color: #1a1a1a;
      --text-color: #f0f0f0;
      --border-color: #444;
      --card-bg: #2d2d2d;
      --card-shadow: 0 2px 5px rgba(0,0,0,0.3);
      --secondary-text: #aaa;
      --primary-accent: #bbdefb;
      --primary-accent-light: #0d47a1;
      --error-color: #f44336;
    }
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      margin: 0;
      padding: 0;
      background-color: var(--bg-color);
      color: var(--text-color);
      transition: background-color 0.3s ease, color 0.3s ease;
    }
    .dashboard {
      display: flex;
      min-height: 100vh;
    }
    .main {
      flex: 1;
      padding: 20px;
      max-width: 1200px;
      margin: 0 auto;
    }
    .topbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid var(--border-color);
    }
    .controls {
      display: flex;
      gap: 20px;
      margin-bottom: 30px;
      flex-wrap: wrap;
    }
    .filter-group {
      display: flex;
      flex-direction: column;
      gap: 5px;
      min-width: 200px;
    }
    label {
      font-weight: 600;
      font-size: 0.9rem;
    }
    select, input {
      padding: 10px 12px;
      border-radius: 6px;
      border: 1px solid var(--border-color);
      background-color: var(--card-bg);
      color: var(--text-color);
      font-size: 1rem;
    }
    button {
      padding: 10px 15px;
      background-color: var(--primary-accent);
      color: white;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      transition: background-color 0.2s;
    }
    button:hover {
      opacity: 0.9;
    }
    .holiday-list {
      list-style: none;
      padding: 0;
      display: grid;
      gap: 15px;
    }
    .holiday-item {
      background: var(--card-bg);
      border-radius: 8px;
      padding: 15px;
      box-shadow: var(--card-shadow);
      display: flex;
      align-items: center;
      gap: 20px;
      transition: transform 0.2s;
    }
    .holiday-item:hover {
      transform: translateY(-3px);
    }
    .holiday-date {
      font-size: 1.2rem;
      font-weight: bold;
      min-width: 80px;
    }
    .holiday-info {
      flex: 1;
    }
    .holiday-info h4 {
      margin: 0 0 5px 0;
      font-size: 1.1rem;
    }
    .local-name {
      font-style: italic;
      color: var(--secondary-text);
      margin: 0;
    }
    .description {
      color: var(--secondary-text);
      margin: 5px 0 0 0;
      font-size: 0.9rem;
    }
    .holiday-type {
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 0.8rem;
      background: var(--primary-accent-light);
      color: var(--primary-accent);
      white-space: nowrap;
    }
    .no-holidays, .loading {
      text-align: center;
      padding: 40px 20px;
      color: var(--secondary-text);
      font-size: 1.1rem;
    }
    .error {
      color: var(--error-color);
      text-align: center;
      padding: 20px;
      font-style: italic;
    }
    .results-info {
      margin-bottom: 15px;
      font-size: 0.9rem;
      color: var(--secondary-text);
    }
    @media (max-width: 768px) {
      .controls {
        flex-direction: column;
        gap: 15px;
      }
      .filter-group {
        min-width: 100%;
      }
      .holiday-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
      }
    }
  </style>
</head>
<body>
  <div class="dashboard">
    <main class="main">
      <header class="topbar">
        <h1>📅 Holiday Calendar</h1>
        <div class="theme-toggle">
          <label>
            <input type="checkbox" id="darkModeToggle">
            <span id="themeLabel">🌗 Dark Mode</span>
          </label>
        </div>
      </header>

      <section class="controls">
        <div class="filter-group">
          <label for="countrySelect">🌍 Country</label>
          <select id="countrySelect">
            <option value="IN">India 🇮🇳</option>
            <option value="US">United States 🇺🇸</option>
            <option value="GB">United Kingdom 🇬🇧</option>
            <option value="DE">Germany 🇩🇪</option>
            <option value="JP">Japan 🇯🇵</option>
            <option value="FR">France 🇫🇷</option>
            <option value="CA">Canada 🇨🇦</option>
            <option value="AU">Australia 🇦🇺</option>
          </select>
        </div>
        <div class="filter-group">
          <label for="yearSelect">📆 Year</label>
          <input type="number" id="yearSelect" min="2000" max="2100" value="<?= date('Y') ?>">
        </div>
        <div class="filter-group">
          <label for="monthSelect">🗓️ Month</label>
          <select id="monthSelect">
            <option value="">All Months</option>
            <?php 
            $months = [
                '01' => 'January', '02' => 'February', '03' => 'March',
                '04' => 'April', '05' => 'May', '06' => 'June',
                '07' => 'July', '08' => 'August', '09' => 'September',
                '10' => 'October', '11' => 'November', '12' => 'December'
            ];
            foreach ($months as $num => $name) {
                $selected = ($num === date('m')) ? 'selected' : '';
                echo "<option value=\"$num\" $selected>$name</option>";
            }
            ?>
          </select>
        </div>
      </section>

      <div class="results-info" id="resultsInfo"></div>
      
      <section class="holiday-section">
        <h2>🎉 Public Holidays</h2>
        <ul class="holiday-list" id="holidayList">
          <li class="loading">Select filters to load holidays</li>
        </ul>
      </section>
    </main>
  </div>

  <script>
    // DOM Elements
    const darkModeToggle = document.getElementById('darkModeToggle');
    const themeLabel = document.getElementById('themeLabel');
    const countrySelect = document.getElementById('countrySelect');
    const yearSelect = document.getElementById('yearSelect');
    const monthSelect = document.getElementById('monthSelect');
    const holidayList = document.getElementById('holidayList');
    const resultsInfo = document.getElementById('resultsInfo');

    // Initialize dark mode from localStorage or prefer-color-scheme
    function initTheme() {
      const savedMode = localStorage.getItem('darkMode');
      const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
      
      if (savedMode === 'enabled' || (!savedMode && systemPrefersDark)) {
        document.body.classList.add('dark-mode');
        darkModeToggle.checked = true;
        themeLabel.textContent = '☀️ Light Mode';
      } else {
        themeLabel.textContent = '🌗 Dark Mode';
      }
    }

    // Toggle dark mode
    darkModeToggle.addEventListener('change', () => {
      document.body.classList.toggle('dark-mode');
      const isDark = document.body.classList.contains('dark-mode');
      localStorage.setItem('darkMode', isDark ? 'enabled' : 'disabled');
      themeLabel.textContent = isDark ? '☀️ Light Mode' : '🌗 Dark Mode';
    });

    // Debounce function to prevent rapid API calls
    function debounce(func, wait) {
      let timeout;
      return function() {
        const context = this, args = arguments;
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(context, args), wait);
      };
    }

    // Fetch holidays from API with error handling
    async function fetchHolidays() {
      const country = countrySelect.value;
      const year = yearSelect.value;
      const month = monthSelect.value;
      
      // Validate year input
      if (year < 2000 || year > 2100) {
        holidayList.innerHTML = '<li class="error">Please enter a year between 2000-2100</li>';
        resultsInfo.textContent = '';
        return;
      }
      
      try {
        holidayList.innerHTML = '<li class="loading">Loading holidays...</li>';
        resultsInfo.textContent = '';
        
        const params = new URLSearchParams({
          action: 'getHolidays',
          year: year,
          ...(country && { country }),
          ...(month && { month })
        });
        
        const response = await fetch(`?${params.toString()}`);
        
        if (!response.ok) {
          throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        
        if (data.success) {
          renderHolidays(data.holidays, country, year, month);
        } else {
          throw new Error(data.message || 'Unknown error occurred');
        }
      } catch (error) {
        console.error('Fetch error:', error);
        holidayList.innerHTML = `<li class="error">Failed to load holidays: ${error.message}</li>`;
      }
    }

    // Render holidays to the DOM
    function renderHolidays(holidays, country, year, month) {
      holidayList.innerHTML = '';
      
      if (!holidays || holidays.length === 0) {
        holidayList.innerHTML = '<li class="no-holidays">No holidays found for the selected filters</li>';
        resultsInfo.textContent = '';
        return;
      }
      
      // Update results info
      const countryName = countrySelect.options[countrySelect.selectedIndex].text;
      const monthName = month ? monthSelect.options[monthSelect.selectedIndex].text : 'All Months';
      resultsInfo.textContent = `Showing ${holidays.length} holidays for ${countryName} in ${monthName}, ${year}`;
      
      holidays.forEach(holiday => {
        const date = new Date(holiday.date);
        const li = document.createElement('li');
        li.className = 'holiday-item';
        li.innerHTML = `
          <div class="holiday-date">
            ${date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })}
          </div>
          <div class="holiday-info">
            <h4>${holiday.name}</h4>
            ${holiday.localName ? `<p class="local-name">${holiday.localName}</p>` : ''}
            ${holiday.description ? `<p class="description">${holiday.description}</p>` : ''}
          </div>
          <div class="holiday-type">${holiday.type}</div>
        `;
        holidayList.appendChild(li);
      });
    }

    // Event listeners with debouncing
    countrySelect.addEventListener('change', debounce(fetchHolidays, 300));
    yearSelect.addEventListener('change', debounce(fetchHolidays, 300));
    monthSelect.addEventListener('change', debounce(fetchHolidays, 300));

    // Input validation for year
    yearSelect.addEventListener('blur', () => {
      if (yearSelect.value < 2000) yearSelect.value = 2000;
      if (yearSelect.value > 2100) yearSelect.value = 2100;
      fetchHolidays();
    });

    // Initial load when page is ready
    document.addEventListener('DOMContentLoaded', () => {
      initTheme();
      fetchHolidays();
    });
  </script>
</body>
</html>