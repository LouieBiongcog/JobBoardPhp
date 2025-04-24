<?php
session_start();
include('db.php');


if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'] ?? 'User';


$query = "SELECT job_applications.id, jobs.title, jobs.description, jobs.location, jobs.company, 
                 job_applications.status, job_applications.applied_at, job_applications.resume
          FROM job_applications
          JOIN jobs ON job_applications.job_id = jobs.id
          WHERE job_applications.user_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Applied Jobs</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: #f5f7fa;
            color: #333;
            line-height: 1.6;
        }
        
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            animation: slideDown 0.5s ease-out;
        }
        
        @keyframes slideDown {
            from {
                transform: translateY(-50px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .header-content h1 {
            font-weight: 600;
            font-size: 28px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .user-greeting {
            font-size: 16px;
        }
        
        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            padding: 8px 16px;
            border-radius: 50px;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: 500;
            text-decoration: none;
        }
        
        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }
        
        .section-title {
            font-size: 24px;
            margin: 30px 0 20px;
            color: #444;
            position: relative;
            padding-left: 15px;
        }
        
        .section-title::before {
            content: '';
            position: absolute;
            left: 0;
            top: 5px;
            height: 20px;
            width: 5px;
            background: linear-gradient(to bottom, #667eea, #764ba2);
            border-radius: 5px;
        }
        
        .jobs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .job-card {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            opacity: 0;
            transform: translateY(20px);
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        @keyframes fadeIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .job-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .job-card-header {
            padding: 20px;
            background: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        
        .job-card-title {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 5px;
        }
        
        .job-card-company {
            font-size: 16px;
            color: #667eea;
            font-weight: 500;
        }
        
        .job-card-body {
            padding: 20px;
        }
        
        .job-card-detail {
            display: flex;
            margin-bottom: 10px;
        }
        
        .job-card-detail i {
            width: 24px;
            color: #666;
            margin-right: 10px;
        }
        
        .job-card-description {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            margin: 15px 0;
        }
        
        .job-card-footer {
            padding: 15px 20px;
            background: #f8f9fa;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .job-status {
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-accepted {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .job-applied-date {
            font-size: 14px;
            color: #666;
        }
        
        .resume-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        
        .resume-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .no-jobs {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            color: #666;
        }
        
        .back-link {
            display: inline-block;
            margin-top: 20px;
            color: #666;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .back-link:hover {
            color: #667eea;
        }
        
        @media (max-width: 768px) {
            .jobs-grid {
                grid-template-columns: 1fr;
            }
            
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div>
                <h1>Your Job Applications</h1>
                <div class="user-greeting">Welcome, <?= htmlspecialchars($username) ?></div>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>
    
    <div class="dashboard-container">
        <h2 class="section-title">Your Applied Jobs</h2>
        
        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="jobs-grid">
                <?php 
                $animation_delay = 0;
                while ($job = mysqli_fetch_assoc($result)): 
                    $animation_delay += 0.1;
                ?>
                    <div class="job-card" style="animation-delay: <?= $animation_delay ?>s">
                        <div class="job-card-header">
                            <h3 class="job-card-title"><?= htmlspecialchars($job['title']) ?></h3>
                            <div class="job-card-company"><?= htmlspecialchars($job['company']) ?></div>
                        </div>
                        
                        <div class="job-card-body">
                            <div class="job-card-detail">
                                <i class="fas fa-map-marker-alt"></i>
                                <span><?= htmlspecialchars($job['location']) ?></span>
                            </div>
                            
                            <p class="job-card-description">
                                <?= htmlspecialchars(substr($job['description'], 0, 150)) . (strlen($job['description']) > 150 ? '...' : '') ?>
                            </p>
                            
                            <a href="<?= htmlspecialchars($job['resume']) ?>" class="resume-link" target="_blank">
                                <i class="fas fa-file-alt"></i> View Submitted Resume
                            </a>
                        </div>
                        
                        <div class="job-card-footer">
                            <span class="job-status status-<?= strtolower($job['status']) ?>">
                                <?= htmlspecialchars($job['status']) ?>
                            </span>
                            <span class="job-applied-date">
                                Applied on <?= date('M j, Y', strtotime($job['applied_at'])) ?>
                            </span>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            
            <a href="user_dashboard.php" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        <?php else: ?>
            <div class="no-jobs">
                <h3>No Applied Jobs Yet</h3>
                <p>You haven't applied for any jobs. Start your job search today!</p>
                <a href="search_jobs.php" class="apply-btn" style="display: inline-block; margin-top: 15px;">
                    Browse Jobs
                </a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>