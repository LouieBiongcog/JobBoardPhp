<?php
session_start();
if ($_SESSION['role'] != 'admin') {
    header('Location: index.php');
    exit();
}
include('db.php');

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
   
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $company = mysqli_real_escape_string($conn, $_POST['company']);
    $user_id = $_SESSION['user_id'];

   
    if (empty($title) || empty($description) || empty($location) || empty($company)) {
        $error = 'All fields are required';
    } else {
        
        $query = "INSERT INTO jobs (title, description, location, company, user_id) 
                  VALUES (?, ?, ?, ?, ?)";
        
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ssssi", $title, $description, $location, $company, $user_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $success = true;
            header('Location: admin_dashboard.php?success=created');
            exit();
        } else {
            $error = "Error: " . mysqli_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Job</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap');
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .create-job-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 600px;
            transform: translateY(0);
            animation: floatUp 0.5s ease-out forwards;
            opacity: 0;
        }
        
        @keyframes floatUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
            font-weight: 600;
        }
        
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e6e6e6;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }
        
        button {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, #667eea, #764ba2);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
            margin-top: 10px;
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }
        
        button:active {
            transform: translateY(0);
        }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        
        .back-link a {
            color: #667eea;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .back-link a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(231, 76, 60, 0.1);
            border-radius: 8px;
            animation: shake 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
        
        .success-message {
            color: #2ecc71;
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(46, 204, 113, 0.1);
            border-radius: 8px;
            animation: floatUp 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="create-job-container">
        <h1>Create New Job Posting</h1>
        
        <?php if($error): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success-message">Job created successfully!</div>
        <?php endif; ?>
        
        <form method="POST" action="create_job.php">
            <div class="form-group">
                <label for="title">Job Title</label>
                <input type="text" name="title" id="title" placeholder="Enter job title" required>
            </div>
            
            <div class="form-group">
                <label for="description">Job Description</label>
                <textarea name="description" id="description" placeholder="Enter detailed job description" required></textarea>
            </div>
            
            <div class="form-group">
                <label for="location">Location</label>
                <input type="text" name="location" id="location" placeholder="Enter job location" required>
            </div>
            
            <div class="form-group">
                <label for="company">Company</label>
                <input type="text" name="company" id="company" placeholder="Enter company name" required>
            </div>
            
            <button type="submit">Create Job Post</button>
        </form>
        
        <div class="back-link">
            <a href="admin_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>

    <script>
       
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.querySelector('.create-job-container');
            container.style.opacity = '1';
            
           
            const inputs = document.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentNode.querySelector('label').style.color = '#667eea';
                });
                input.addEventListener('blur', function() {
                    if (!this.value) {
                        this.parentNode.querySelector('label').style.color = '#555';
                    }
                });
            });
        });
        
       
        if (!document.querySelector('.fa-arrow-left')) {
            const fa = document.createElement('link');
            fa.rel = 'stylesheet';
            fa.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css';
            document.head.appendChild(fa);
        }
    </script>
</body>
</html>