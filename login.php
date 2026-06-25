<?php
require_once 'config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AuraCRM - Secure Login</title>
    
    <!-- Google Fonts Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary: #f97316;
            --primary-hover: #ea580c;
            --primary-glow: rgba(249, 115, 22, 0.15);
            --bg-main: #f8fafc;
            --bg-card: #ffffff;
            --text-primary: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
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
            padding: 40px;
            border-radius: var(--radius-lg);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border);
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
            border: 1px solid #cbd5e1;
            border-radius: var(--radius-md);
            font-size: 14px;
            color: var(--text-primary);
            background-color: #ffffff;
            transition: border-color 0.15s ease, box-shadow 0.15s ease;
            width: 100%;
        }

        input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-glow);
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 12px;
            border-radius: var(--radius-md);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            background-color: var(--primary);
            color: white;
            transition: background-color 0.15s ease;
            width: 100%;
            margin-top: 10px;
        }

        .btn:hover {
            background-color: var(--primary-hover);
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="brand-container">
            <div class="brand-logo">A</div>
            <div class="brand-name">AuraCRM</div>
        </div>
        <h2>Welcome Back</h2>
        <form id="login-form" onsubmit="handleLogin(event)">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <button type="submit" class="btn">Secure Login</button>
        </form>
    </div>

    <script>
        function handleLogin(e) {
            e.preventDefault();
            const fd = new FormData(e.target);
            fetch('?api=login', {method: 'POST', body: fd})
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (data.role === 'Staff') {
                        location.href = 'staff/dashboard.php';
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
