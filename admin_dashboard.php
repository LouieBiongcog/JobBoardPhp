<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}
include('db.php');


$jobQuery = "SELECT * FROM jobs";
$jobResult = mysqli_query($conn, $jobQuery);


if (isset($_GET['action']) && isset($_GET['application_id'])) {
    $appId = intval($_GET['application_id']);
    $newStatus = ($_GET['action'] == 'accept') ? 'accepted' : 'rejected';
    $updateQuery = "UPDATE job_applications SET status = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($stmt, "si", $newStatus, $appId);
    mysqli_stmt_execute($stmt);
}


$appQuery = "
    SELECT job_applications.id, jobs.title AS job_title, users.username, users.email, 
           job_applications.resume, job_applications.status, job_applications.applied_at
    FROM job_applications
    JOIN jobs ON job_applications.job_id = jobs.id
    JOIN users ON job_applications.user_id = users.id
    ORDER BY job_applications.applied_at DESC
";
$appResult = mysqli_query($conn, $appQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
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
        
        .create-job-btn {
            display: inline-block;
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            margin-bottom: 30px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .create-job-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        .jobs-table, .applications-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 40px;
            animation: fadeIn 0.8s ease-out;
        }
        
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .jobs-table th, .jobs-table td,
        .applications-table th, .applications-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .jobs-table th, .applications-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        
        .jobs-table tr:last-child td,
        .applications-table tr:last-child td {
            border-bottom: none;
        }
        
        .jobs-table tr:hover,
        .applications-table tr:hover {
            background: #f9f9f9;
        }
        
        .action-btn {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            margin-right: 8px;
        }
        
        .edit-btn {
            background: #4CAF50;
            color: white;
        }
        
        .delete-btn {
            background: #f44336;
            color: white;
        }
        
        .accept-btn {
            background: #4CAF50;
            color: white;
        }
        
        .reject-btn {
            background: #f44336;
            color: white;
        }
        
        .resume-link {
            color: #2196F3;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .resume-link:hover {
            color: #0b7dda;
            text-decoration: underline;
        }
        
        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        
        .status-pending {
            color: #FF9800;
            font-weight: 500;
        }
        
        .status-accepted {
            color: #4CAF50;
            font-weight: 500;
        }
        
        .status-rejected {
            color: #f44336;
            font-weight: 500;
        }
        
        .no-jobs, .no-applications {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            color: #666;
            margin-bottom: 40px;
        }
        
        .flash-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            background: #4CAF50;
            color: white;
            text-align: center;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(-100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        @media (max-width: 768px) {
            .jobs-table, .applications-table {
                display: block;
                overflow-x: auto;
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
            <h1>Admin Dashboard</h1>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>
    
    <div class="dashboard-container">
        <?php if(isset($_GET['success'])): ?>
            <div class="flash-message">
                <?php 
                if($_GET['success'] == 'created') echo "Job created successfully!";
                if($_GET['success'] == 'updated') echo "Job updated successfully!";
                if($_GET['success'] == 'deleted') echo "Job deleted successfully!";
                ?>
            </div>
        <?php endif; ?>

        <a href="create_job.php" class="create-job-btn">
            <i class="fas fa-plus"></i> Create New Job Post
        </a>

       
        <h2 class="section-title">Job Listings</h2>
        <?php if(mysqli_num_rows($jobResult) > 0): ?>
            <table class="jobs-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Location</th>
                        <th>Company</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($job = mysqli_fetch_assoc($jobResult)): ?>
                    <tr>
                        <td><?= htmlspecialchars($job['title']) ?></td>
                        <td><?= htmlspecialchars(substr($job['description'], 0, 100)) . (strlen($job['description']) > 100 ? '...' : '') ?></td>
                        <td><?= htmlspecialchars($job['location']) ?></td>
                        <td><?= htmlspecialchars($job['company']) ?></td>
                        <td>
                            <a href="edit_job.php?id=<?= $job['id'] ?>" class="action-btn edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="delete_job.php?id=<?= $job['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Are you sure you want to delete this job?')">
                                <i class="fas fa-trash"></i> Delete
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-jobs">
                <h3>No jobs found</h3>
                <p>Create your first job posting using the button above</p>
            </div>
        <?php endif; ?>

       
        <h2 class="section-title">Job Applications</h2>
        <?php if(mysqli_num_rows($appResult) > 0): ?>
            <table class="applications-table">
                <thead>
                    <tr>
                        <th>Job Title</th>
                        <th>Applicant</th>
                        <th>Email</th>
                        <th>Resume</th>
                        <th>Status</th>
                        <th>Applied At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                <?php while ($app = mysqli_fetch_assoc($appResult)): ?>
                    <tr>
                        <td><?= htmlspecialchars($app['job_title']) ?></td>
                        <td><?= htmlspecialchars($app['username']) ?></td>
                        <td><?= htmlspecialchars($app['email']) ?></td>
                        <td>
                            <a class="resume-link" href="<?= htmlspecialchars($app['resume']) ?>" target="_blank">
                                <i class="fas fa-file-alt"></i> View
                            </a>
                        </td>
                        <td>
                            <span class="status-<?= htmlspecialchars(strtolower($app['status'])) ?>">
                                <?= htmlspecialchars($app['status']) ?>
                            </span>
                        </td>
                        <td><?= date('M j, Y g:i a', strtotime($app['applied_at'])) ?></td>
                        <td>
                            <?php if ($app['status'] == 'pending'): ?>
                                <a href="?action=accept&application_id=<?= $app['id'] ?>" class="action-btn accept-btn">
                                    <i class="fas fa-check"></i> Accept
                                </a>
                                <a href="?action=reject&application_id=<?= $app['id'] ?>" class="action-btn reject-btn">
                                    <i class="fas fa-times"></i> Reject
                                </a>
                            <?php else: ?>
                                <em><?= ucfirst(htmlspecialchars($app['status'])) ?></em>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-applications">
                <h3>No applications found</h3>
                <p>Applications will appear here when users apply for jobs</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
       
        document.addEventListener('DOMContentLoaded', () => {
            const jobRows = document.querySelectorAll('.jobs-table tbody tr');
            const appRows = document.querySelectorAll('.applications-table tbody tr');
            
            jobRows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(10px)';
                row.style.animation = `fadeIn 0.5s ease-out ${index * 0.1}s forwards`;
            });
            
            appRows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(10px)';
                row.style.animation = `fadeIn 0.5s ease-out ${index * 0.1 + 0.3}s forwards`;
            });
            
           
            const flashMessage = document.querySelector('.flash-message');
            if (flashMessage) {
                setTimeout(() => {
                    flashMessage.style.opacity = '0';
                    flashMessage.style.transform = 'translateY(-20px)';
                    flashMessage.style.transition = 'all 0.5s ease';
                    setTimeout(() => flashMessage.remove(), 500);
                }, 3000);
            }
        });
    </script>
</body>
</html>