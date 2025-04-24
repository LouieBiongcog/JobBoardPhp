<?php
session_start();


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}


$username = isset($_SESSION['username']) ? $_SESSION['username'] : 'User'; 

include('db.php');


$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';


$query = "SELECT * FROM jobs WHERE title LIKE '%$search%' OR description LIKE '%$search%' OR location LIKE '%$search%' OR company LIKE '%$search%'";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
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
        
        .welcome-message {
            font-size: 18px;
            margin-bottom: 5px;
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
        
        .jobs-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
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
        
        .jobs-table th, .jobs-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .jobs-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: #555;
        }
        
        .jobs-table tr:last-child td {
            border-bottom: none;
        }
        
        .jobs-table tr:hover {
            background: #f9f9f9;
        }
        
        .apply-btn {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(to right, #667eea, #764ba2);
            color: white;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
        }
        
        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(102, 126, 234, 0.3);
        }
        
        .no-jobs {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            color: #666;
        }
        
        .job-description {
            color: #666;
            font-size: 14px;
            line-height: 1.5;
            max-width: 500px;
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
            .jobs-table {
                display: block;
                overflow-x: auto;
            }
            
            .header-content {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
        }
        .search-bar {
        margin: 20px 0;
        display: flex;
        justify-content: center;
        }

        .search-bar form {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #f2f2f2;
            padding: 10px 20px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }

        .search-bar input[type="text"] {
            padding: 10px;
            width: 300px;
            border: 1px solid #ccc;
            border-radius: 6px;
            font-size: 16px;
        }

        .search-bar button {
            padding: 10px 16px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .search-bar button:hover {
            background-color: #0056b3;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <header>
        <div class="header-content">
            <div>
                <h1>Job Portal</h1>
                
                <div class="welcome-message">Welcome, <?= htmlspecialchars($username) ?>!</div>
            </div>
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </header>
    
    <div class="dashboard-container">
        <?php if(isset($_GET['application']) && $_GET['application'] === 'success'): ?>
            <div class="flash-message">
                Application submitted successfully!
            </div>
        <?php endif; ?>

        <a href="view_applied_jobs.php" style="display: inline-block; padding: 10px 20px; background-color: #007bff; color: white; text-decoration: none; border-radius: 5px; font-weight: 500; transition: background-color 0.3s ease;">View Your Applied Jobs</a>



        <div class="search-bar">
            <form method="GET" action="">
                <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search jobs by title, location, or company...">
                <button type="submit">Search</button>
            </form>
        </div>
        
        <h2 class="section-title">Available Jobs</h2>
        
        <?php if(mysqli_num_rows($result) > 0): ?>
            <table class="jobs-table">
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($job = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($job['title']) ?></strong></td>
                        <td class="job-description"><?= htmlspecialchars(substr($job['description'], 0, 150)) . (strlen($job['description']) > 150 ? '...' : '') ?></td>
                        <td>
                            <a href="apply_for_job.php?job_id=<?= $job['id'] ?>" class="apply-btn">Apply Now</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="no-jobs">
                <h3>No matching jobs found<?= $search ? " for '$search'" : '' ?></h3>
                <p>Please try different keywords.</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const rows = document.querySelectorAll('.jobs-table tbody tr');
            rows.forEach((row, index) => {
                row.style.opacity = '0';
                row.style.transform = 'translateY(10px)';
                row.style.animation = `fadeIn 0.5s ease-out ${index * 0.1}s forwards`;
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
