<?php
session_start();
include('db.php');


if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header('Location: login.php');
    exit();
}

$job_id = isset($_GET['job_id']) ? (int)$_GET['job_id'] : 0;

$job_query = "SELECT * FROM jobs WHERE id = ?";
$stmt = mysqli_prepare($conn, $job_query);
mysqli_stmt_bind_param($stmt, "i", $job_id);
mysqli_stmt_execute($stmt);
$job_result = mysqli_stmt_get_result($stmt);
$job = mysqli_fetch_assoc($job_result);

if (!$job) {
    header("Location: user_dashboard.php");
    exit();
}

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];

    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $resume = $_FILES['resume'];

        $allowed_types = [
            'application/pdf' => 'pdf',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx'
        ];
        $max_size = 5 * 1024 * 1024; 

       
        if (!array_key_exists($resume['type'], $allowed_types)) {
            $error = "Only PDF or Word documents are allowed.";
        } elseif ($resume['size'] > $max_size) {
            $error = "Resume must be less than 5MB.";
        } else {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

          
            $file_ext = $allowed_types[$resume['type']];
            $new_filename = 'resume_' . bin2hex(random_bytes(8)) . '.' . $file_ext;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($resume['tmp_name'], $upload_path)) {
                $stmt = mysqli_prepare($conn, "INSERT INTO job_applications (job_id, user_id, resume) VALUES (?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "iis", $job_id, $user_id, $upload_path);

                if (mysqli_stmt_execute($stmt)) {
                    $success = true;
                    header("Refresh:3; url=user_dashboard.php");
                } else {
                    $error = "Database error: " . mysqli_error($conn);
                    unlink($upload_path); 
                }
            } else {
                $error = "Failed to upload resume.";
            }
        }
    } else {
        $error = "Please select a resume to upload.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for <?= htmlspecialchars($job['title']) ?></title>
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
        
        .application-container {
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
        
        .job-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .job-header h2 {
            color: #333;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .job-header p {
            color: #667eea;
            font-size: 18px;
            font-weight: 500;
        }
        
        .file-upload-wrapper {
            position: relative;
            margin-bottom: 25px;
        }
        
        .file-upload-label {
            display: block;
            padding: 40px 20px;
            border: 2px dashed #e6e6e6;
            border-radius: 8px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-upload-label:hover {
            border-color: #667eea;
            background: rgba(102, 126, 234, 0.05);
        }
        
        .file-upload-label i {
            font-size: 48px;
            color: #667eea;
            margin-bottom: 15px;
            display: block;
        }
        
        .file-upload-label span {
            color: #667eea;
            font-weight: 500;
            font-size: 18px;
        }
        
        .file-upload-label small {
            display: block;
            margin-top: 10px;
            color: #999;
        }
        
        .file-upload-input {
            position: absolute;
            left: 0;
            top: 0;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
        
        .file-name {
            text-align: center;
            margin-top: 10px;
            color: #666;
            font-size: 14px;
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
        }
        
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
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
        
        .success-message {
            color: #2ecc71;
            text-align: center;
            margin-bottom: 20px;
            padding: 15px;
            background: rgba(46, 204, 113, 0.1);
            border-radius: 8px;
            animation: floatUp 0.5s ease;
        }
        
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%, 60% { transform: translateX(-5px); }
            40%, 80% { transform: translateX(5px); }
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="application-container">
        <div class="job-header">
            <h2>Job Application</h2>
            <p><?= htmlspecialchars($job['title']) ?></p>
        </div>
        
        <?php if($error): ?>
            <div class="error-message"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if($success): ?>
            <div class="success-message">
                Application submitted successfully! Redirecting to dashboard...
            </div>
        <?php endif; ?>
        
        <?php if(!$success): ?>
            <form method="POST" action="apply_for_job.php?job_id=<?= $job_id ?>" enctype="multipart/form-data">
                <div class="file-upload-wrapper">
                    <label class="file-upload-label">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <span>Upload Your Resume</span>
                        <small>(PDF or Word document, max 5MB)</small>
                        <input type="file" name="resume" class="file-upload-input" required accept=".pdf,.doc,.docx">
                    </label>
                    <div class="file-name" id="file-name">No file selected</div>
                </div>
                
                <button type="submit">
                    <i class="fas fa-paper-plane"></i> Submit Application
                </button>
            </form>
        <?php endif; ?>
        
        <div class="back-link">
            <a href="user_dashboard.php"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
        </div>
    </div>

    <script>
        
        document.querySelector('.file-upload-input').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'No file selected';
            document.getElementById('file-name').textContent = fileName;
        });
        
       
        document.addEventListener('DOMContentLoaded', () => {
            const container = document.querySelector('.application-container');
            container.style.opacity = '1';
        });
    </script>
</body>
</html>