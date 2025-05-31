<?php
// Start session securely
if (session_status() === PHP_SESSION_NONE) {
    session_start([
        'cookie_secure' => true,
        'cookie_httponly' => true,
        'use_strict_mode' => true
    ]);
}

// Verify authentication
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Set page title and include necessary files
$pageTitle = "Team Leaves";
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/db.php';

// Set default timezone
date_default_timezone_set('UTC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <title><?php echo htmlspecialchars($pageTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="stylesheet" href="/css/style.css?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'].'/css/style.css'); ?>" />
  <link rel="stylesheet" href="/css/sidebar.css?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'].'/css/sidebar.css'); ?>" />
  <link rel="stylesheet" href="/css/teamleaves.css?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'].'/css/teamleaves.css'); ?>" />
</head>
<body>
  <!-- Sidebar -->
  <div id="sidebar-container">
    <?php include __DIR__ . '/sidebar.php'; ?>
  </div>

  <!-- Main Section -->
  <main class="main">
    <section class="team-leave-section">
      <div class="section-header">
        <h2><i class="fas fa-users"></i> Team on Leave</h2>
        <div class="date-filter">
          <input type="date" id="leaveDateFilter" value="<?php echo date('Y-m-d'); ?>" />
          <button id="refreshLeaves" class="btn-refresh"><i class="fas fa-sync-alt"></i></button>
        </div>
      </div>
      
      <div class="team-leave-container">
        <?php
        try {
            // Get today's leaves
            $selectedDate = date('Y-m-d');
            $teamId = $_SESSION['team_id'] ?? null;
            
            $query = "
                SELECT 
                    u.id, 
                    u.name, 
                    u.avatar, 
                    l.leave_type,
                    l.start_date,
                    l.end_date,
                    l.reason,
                    d.name AS department
                FROM users u
                JOIN leaves l ON u.id = l.user_id
                LEFT JOIN departments d ON u.department_id = d.id
                WHERE :selected_date BETWEEN l.start_date AND l.end_date
                AND l.status = 'approved'
                AND u.team_id = :team_id
                AND u.status = 'active'
                ORDER BY l.leave_type DESC, u.name ASC
            ";
            
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':selected_date' => $selectedDate,
                ':team_id' => $teamId
            ]);
            $leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($leaves) > 0): ?>
                <ul class="team-leave-list" id="teamLeaveList">
                    <?php foreach ($leaves as $leave): 
                        $isHalfDay = $leave['leave_type'] === 'half';
                        $isFullDay = $leave['leave_type'] === 'full';
                        $isMultiDay = $leave['start_date'] !== $leave['end_date'];
                    ?>
                        <li class="leave-entry <?php echo $isHalfDay ? 'half-day' : ''; ?>">
                            <div class="avatar-container">
                                <img src="<?php echo htmlspecialchars($leave['avatar'] ?? '/images/default-avatar.png', ENT_QUOTES, 'UTF-8'); ?>" 
                                     alt="<?php echo htmlspecialchars($leave['name'], ENT_QUOTES, 'UTF-8'); ?>" 
                                     class="avatar" 
                                     loading="lazy">
                                <?php if ($isHalfDay): ?>
                                    <span class="badge half-day-badge">½</span>
                                <?php endif; ?>
                            </div>
                            <div class="leave-info">
                                <div class="name-dept">
                                    <span class="name"><?php echo htmlspecialchars($leave['name'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php if (!empty($leave['department'])): ?>
                                        <span class="department"><?php echo htmlspecialchars($leave['department'], ENT_QUOTES, 'UTF-8'); ?></span>
                                    <?php endif; ?>
                                </div>
                                <div class="leave-details">
                                    <?php if ($isMultiDay): ?>
                                        <span class="date-range">
                                            <?php echo date('M j', strtotime($leave['start_date'])); ?> - 
                                            <?php echo date('M j', strtotime($leave['end_date'])); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="leave-type">
                                        <?php echo htmlspecialchars(ucfirst($leave['leave_type']), ENT_QUOTES, 'UTF-8'); ?> Leave
                                    </span>
                                </div>
                                <?php if (!empty($leave['reason'])): ?>
                                    <div class="leave-reason" title="<?php echo htmlspecialchars($leave['reason'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <i class="fas fa-comment-alt"></i> 
                                        <?php echo htmlspecialchars(
                                            strlen($leave['reason']) > 50 
                                                ? substr($leave['reason'], 0, 47) . '...' 
                                                : $leave['reason'],
                                            ENT_QUOTES, 'UTF-8'
                                        ); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="leave-stats">
                    <span class="stat-item">
                        <i class="fas fa-user-clock"></i> 
                        <strong><?php echo count($leaves); ?></strong> team members on leave
                    </span>
                    <span class="stat-item">
                        <i class="fas fa-calendar-day"></i> 
                        <?php echo date('F j, Y', strtotime($selectedDate)); ?>
                    </span>
                </div>
            <?php else: ?>
                <div class="no-leaves-message">
                    <div class="celebration-icon">
                        <i class="fas fa-glass-cheers"></i>
                    </div>
                    <h3>Full Team Today!</h3>
                    <p>Everyone is available on <?php echo date('F j, Y', strtotime($selectedDate)); ?></p>
                    <button id="viewUpcomingLeaves" class="btn-secondary">
                        <i class="fas fa-calendar-alt"></i> View Upcoming Leaves
                    </button>
                </div>
            <?php endif;
            
        } catch (PDOException $e) {
            error_log("Database error in team leaves: " . $e->getMessage());
            ?>
            <div class="error-state">
                <i class="fas fa-exclamation-triangle"></i>
                <h3>Unable to Load Leave Data</h3>
                <p>We're having trouble loading the team leave information. Please try again later.</p>
                <button id="retryLoad" class="btn-primary">
                    <i class="fas fa-redo"></i> Retry
                </button>
            </div>
            <?php
        }
        ?>
      </div>
    </section>
    
    <!-- Upcoming Leaves Modal (hidden by default) -->
    <div id="upcomingLeavesModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-calendar-week"></i> Upcoming Team Leaves</h3>
                <button class="close-modal">&times;</button>
            </div>
            <div class="modal-body">
                <div id="upcomingLeavesContainer">
                    <p>Loading upcoming leaves...</p>
                </div>
            </div>
            <div class="modal-footer">
                <button id="exportLeaves" class="btn-secondary">
                    <i class="fas fa-file-export"></i> Export to CSV
                </button>
            </div>
        </div>
    </div>
  </main>

  <script src="/js/script.js?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'].'/js/script.js'); ?>"></script>
  <script src="/js/team-leaves.js?v=<?php echo filemtime($_SERVER['DOCUMENT_ROOT'].'/js/team-leaves.js'); ?>"></script>
</body>
</html>