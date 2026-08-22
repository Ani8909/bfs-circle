<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BFS Financial Services - Secure Login</title>
    
    <!-- Google Fonts Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #10b981;
            --primary-hover: #059669;
            --secondary: #f97316;
            --primary-glow: rgba(16, 185, 129, 0.25);
            --bg-main: #000000;
            --bg-card: rgba(10, 10, 10, 0.65);
            --text-primary: #ffffff;
            --text-muted: #a1a1aa;
            --border: rgba(255, 255, 255, 0.12);
            --radius-md: 10px;
            --radius-lg: 16px;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
        }

        body {
            background-color: var(--bg-main);
            background-image: url('login.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            color: var(--text-primary);
            display: flex;
            height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 400px;
            background-color: var(--bg-card);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: var(--radius-lg);
            box-shadow: 0 15px 45px rgba(0, 0, 0, 0.6), inset 0 1px 0 rgba(255,255,255,0.1);
            border: 1px solid var(--border);
            border-top: 1px solid rgba(249, 115, 22, 0.4);
            border-bottom: 1px solid rgba(16, 185, 129, 0.4);
        }

        .brand-container {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            margin-bottom: 30px;
        }

        .brand-logo {
            background-color: var(--primary);
            color: white;
            width: 38px;
            height: 38px;
            border-radius: var(--radius-md);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 20px;
            box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3);
        }

        .brand-name {
            font-family: 'Outfit', sans-serif;
            font-weight: 700;
            font-size: 24px;
            letter-spacing: -0.5px;
            color: var(--text-primary);
        }

        h2 {
            font-family: 'Outfit', sans-serif;
            font-weight: 600;
            font-size: 20px;
            text-align: center;
            margin-bottom: 24px;
            color: var(--text-primary);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 6px;
            margin-bottom: 18px;
        }

        label {
            font-size: 13px;
            font-weight: 500;
            color: var(--text-primary);
        }

        input[type="text"],
        input[type="password"] {
            padding: 10px 14px;
            border: 1px solid var(--border);
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--text-primary);
            background-color: rgba(255, 255, 255, 0.05);
            transition: border-color 0.15s ease, box-shadow 0.15s ease, background-color 0.15s ease;
            width: 100%;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
            background-color: rgba(255, 255, 255, 0.1);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            border: none;
            background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
            background-size: 200% auto;
            color: white;
            transition: background-position 0.5s ease, transform 0.2s;
            width: 100%;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.3);
        }

        .btn:hover {
            background-position: right center;
        }

        .password-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }

        .eye-icon {
            position: absolute;
            right: 12px;
            cursor: pointer;
            color: #94a3b8;
            font-size: 16px;
            user-select: none;
        }
        
        .eye-icon:hover {
            color: #475569;
        }
    </style>
    <!-- Add FontAwesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="login-container">
        <div class="brand-container">
            <img src="logo.png" alt="BFS Financial Services Logo" style="height: 48px; width: auto; object-fit: contain; filter: brightness(0) invert(1);">
            <div class="brand-name">BFS Financial Services</div>
        </div>
        <h2>Welcome Back</h2>
        <form id="login-form" onsubmit="handleLogin(event)">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password_input" name="password" required>
                    <i class="fas fa-eye eye-icon" id="togglePassword" onclick="togglePasswordVisibility()"></i>
                </div>
            </div>
            <button type="submit" class="btn">Secure Login</button>
        </form>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password_input');
            const toggleIcon = document.getElementById('togglePassword');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        async function handleLogin(e) {
            e.preventDefault();
            const fd = new FormData(e.target);
            fetch('?api=login', {method: 'POST', body: fd})
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.role === 'Staff') {
                          location.href = 'staff/index.php';
                      } else if (['Partner', 'DSA', 'Connector', 'Individual'].includes(data.role)) {
                          location.href = 'partner/index.php';
                      } else if (data.role === 'Builder') {
                          location.href = 'builder/index.php';
                      } else if (data.role === 'Agent') {
                          location.href = 'agent/index.php';
                      } else if (['CA', 'CA/CS'].includes(data.role)) {
                          location.href = 'ca/index.php';
                      } else {
                          location.href = 'dashboard.php';
                      }
                } else {
                    alert(data.error);
                }
            })
            .catch(err => {
                console.error(err);
                alert("Connection failed.");
            });
        }
    </script>
</body>
</html>
