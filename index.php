<?php
// index.php (EDUMEET - FINAL DESIGN WITH PRESERVED TEXT)
session_start();

// Giriş yapmışsa home.php'ye yönlendir
if (isset($_SESSION['user_id'])) {
    header("Location: home.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduMeet - Peer Tutoring Platform</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        /* --- GENEL AYARLAR (LIGHT THEME) --- */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body {
            width: 100%;
            background-color: #f8faff; /* Genel sayfa için çok açık gri-mavi */
            color: #333;
            overflow-x: hidden;
        }

        /* --- NAVBAR (BEYAZ & GÖLGELİ) --- */
        .navbar {
            position: fixed; top: 0; left: 0; width: 100%; padding: 15px 50px;
            display: flex; justify-content: space-between; align-items: center; z-index: 1000;
            background: rgba(255, 255, 255, 0.95); /* Hafif şeffaf beyaz */
            backdrop-filter: blur(10px);
            box-shadow: 0 2px 15px rgba(0,0,0,0.05);
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .logo { 
            font-size: 1.8em; font-weight: 800; color: #4361ee; /* Ana Mavi Renk */
            text-decoration: none; letter-spacing: 0.5px; 
        }
        .nav-buttons { display: flex; gap: 15px; }

        .btn { text-decoration: none; padding: 10px 25px; border-radius: 50px; font-weight: 600; transition: 0.3s; font-size: 0.9em; }
        .btn-login { color: #4361ee; border: 2px solid #4361ee; background: transparent; }
        .btn-login:hover { background: #4361ee; color: white; }
        .btn-register { background-color: #4361ee; color: white; border: 2px solid #4361ee; box-shadow: 0 4px 10px rgba(67, 97, 238, 0.3); }
        .btn-register:hover { background-color: #304ffe; border-color: #304ffe; transform: translateY(-2px); }

        /* --- HERO SECTION (ARKAPLAN DESENLİ) --- */
        .hero-section {
            min-height: 100vh;
            display: flex; flex-direction: column; justify-content: center; align-items: center;
            padding: 130px 20px 60px 20px;
            position: relative;
            overflow: hidden;
            /* MODERN ARKAPLAN DESENİ (CSS İLE) */
            background-color: #eef2f7;
            background-image: 
                radial-gradient(at 10% 20%, rgba(67, 97, 238, 0.15) 0px, transparent 40%),
                radial-gradient(at 90% 80%, rgba(44, 62, 80, 0.1) 0px, transparent 40%),
                radial-gradient(at 50% 50%, rgba(255, 255, 255, 0.8) 0px, transparent 60%);
            background-size: 100% 100%;
        }

        /* YENİ: HERO İÇİN ANA KART (CONTAINER) */
        .hero-container-card {
            background: rgba(255, 255, 255, 0.85); /* Yarı saydam beyaz */
            backdrop-filter: blur(20px); /* Buzlu cam efekti */
            border-radius: 30px;
            padding: 60px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.08); /* Yumuşak gölge */
            border: 1px solid rgba(255, 255, 255, 0.6);
            max-width: 1200px;
            width: 100%;
        }
        
        #imagediv {
            display: flex; align-items: center; justify-content: space-between;
            gap: 60px;
        }

        .hero-content { flex: 1; min-width: 300px; }
        .hero-content h1 { 
            font-size: 3.5em; font-weight: 800; margin-bottom: 25px; line-height: 1.2; color: #2c3e50; 
        }
        .hero-content h1 span { color: #4361ee; position: relative; }
        .hero-content p { font-size: 1.15em; font-weight: 400; color: #666; margin-bottom: 40px; line-height: 1.7; }
        .btn-cta { padding: 15px 40px; font-size: 1.1em; background: #4361ee; border-color: #4361ee; }
        .btn-cta:hover { background: #304ffe; border-color: #304ffe; }

        /* Resim Çerçeveleme */
        #imagediv img { 
            width: 100%; max-width: 500px; height: auto; border-radius: 25px; object-fit: cover; 
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
            border: 8px solid rgba(255,255,255,0.9); /* Kalın beyaz çerçeve */
        }

        /* --- STATISTICS BAR (KART ÜSTÜNDE) --- */
        .stats-bar {
            background: #ffffff;
            display: flex; justify-content: space-around; align-items: center;
            padding: 40px 0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-radius: 20px;
            max-width: 1000px; margin: -50px auto 80px auto; /* Kartın üzerine binsin */
            position: relative; z-index: 10;
            border: 1px solid #f0f0f0;
        }
        .stat-item { text-align: center; }
        .stat-number { font-size: 2.5em; display:block; margin-bottom: 5px; } /* İkon boyutu */
        .stat-label { font-size: 1.1em; font-weight: 700; color: #2c3e50; display: block; }
        .stat-desc { font-size: 0.9em; color: #888; display: block;}

        /* --- FEATURES SECTION --- */
        .features-section { padding: 50px 50px 100px 50px; }
        .section-title { text-align: center; font-size: 2.5em; margin-bottom: 60px; font-weight: 700; color: #2c3e50; }
        .features-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 40px; max-width: 1200px; margin: 0 auto;
        }
        .feature-card {
            background: #ffffff; padding: 40px 30px; border-radius: 20px;
            text-align: center; border: 2px solid #f0f4f8; transition: 0.3s;
        }
        .feature-card:hover { transform: translateY(-10px); border-color: #4361ee; box-shadow: 0 15px 40px rgba(67, 97, 238, 0.1); }
        .feature-icon { font-size: 3.5em; margin-bottom: 20px; display: block; }
        .feature-card h3 { font-size: 1.4em; margin-bottom: 15px; color: #333; font-weight: 700; }
        .feature-card p { color: #666; line-height: 1.6; }

        /* --- ABOUT SECTION (YENİ ÇERÇEVELİ KUTU) --- */
        .about-section-wrapper {
            padding: 50px 20px 100px 20px;
            background-color: #d5dbeeff; /* Hafif farklı bir ton */
        }
        .about-container-card {
            background: #ffffff;
            border-radius: 30px;
            padding: 70px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.05);
            border: 1px solid #eef2f7;
            max-width: 1200px;
            margin: 0 auto;
            display: flex; align-items: center; gap: 70px;
        }
        
        /* Resim İçin Paspartu Efekti */
        .about-img { flex: 1; display:flex; justify-content:center; }
        .about-img img { 
            width: 100%; max-width:500px; border-radius: 20px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            border: 6px solid #f4f7f6; /* İç çerçeve rengi */
            padding: 5px; background: white;
        }
        .about-text { flex: 1; }
        .about-text h2 { font-size: 2.5em; margin-bottom: 25px; color: #2c3e50; font-weight: 700; }
        .about-text p { font-size: 1.1em; line-height: 1.8; color: #555; margin-bottom: 25px; }

        /* --- FOOTER --- */
        footer { background: #2c3e50; padding: 70px 20px; text-align: center; color: white; }
        .footer-logo { font-size: 2em; font-weight: 800; margin-bottom: 25px; display: block; color: white; text-decoration: none; }
        .footer-links a { color: #a0aec0; text-decoration: none; margin: 0 20px; transition: 0.3s; font-weight: 500; }
        .footer-links a:hover { color: #4361ee; }
        .copyright { margin-top: 50px; opacity: 0.6; font-size: 0.9em; border-top: 1px solid rgba(255,255,255,0.1); padding-top: 30px; }

        /* Mobil Uyum */
        @media (max-width: 1000px) {
            .hero-section { padding-top: 100px; }
            .hero-container-card { padding: 40px 30px; }
            #imagediv { flex-direction: column-reverse; text-align: center; gap: 40px; }
            #imagediv img { width: 100%; max-width: 400px; }
            .hero-content h1 { font-size: 2.8em; }
            
            .stats-bar { flex-direction: column; gap: 30px; padding: 30px; margin-top: 30px; }
            
            .about-container-card { flex-direction: column; padding: 40px 30px; text-align: center; gap: 40px; }
            .navbar { padding: 15px 20px; }
        }
    </style>
</head>
<body>

    <header class="navbar">
        <a href="index.php" class="logo">EduMeet.</a>
        <div class="nav-buttons">
            <a href="login.php" class="btn btn-login">Login</a>
            <a href="register.php" class="btn btn-register">Register</a>
        </div>
    </header>

    <section class="hero-section">
        <div class="hero-container-card">
            <div id="imagediv">
                <div class="hero-content">
                    <h1>Learn Together,<br> <span>Grow Together.</span></h1>
                    <p>Join EduMeet to connect with peers, share your knowledge, and master new skills through collaborative tutoring in a friendly environment.</p>
                    <a href="register.php" class="btn btn-register btn-cta">Get Started Now</a>
                </div>
                <img src="indeximage.jpeg" alt="Students Learning">
            </div>    
        </div>
    </section>

    <div class="stats-bar">
        <div class="stat-item">
            <span class="stat-number">🗓️</span>
            <span class="stat-label">Flexible</span>
            <span class="stat-desc">Scheduling</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">👨‍🏫</span>
            <span class="stat-label">Verified</span>
            <span class="stat-desc">Campus Tutors</span>
        </div>
        <div class="stat-item">
            <span class="stat-number">🏅</span>
            <span class="stat-label">Quality</span>
            <span class="stat-desc">Education</span>
        </div>
    </div>

    <section class="features-section">
        <h2 class="section-title">Why Choose EduMeet?</h2>
        <div class="features-grid">
            <div class="feature-card">
                <span class="feature-icon">🚀</span>
                <h3>Fast & Easy</h3>
                <p>Find a tutor or a student in seconds. Our smart matching system connects you with the right peers instantly.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">🛡️</span>
                <h3>Safe Environment</h3>
                <p>We prioritize safety. All tutors are verified students from your campus, creating a trusted community.</p>
            </div>
            <div class="feature-card">
                <span class="feature-icon">💡</span>
                <h3>Collaborative Learning</h3>
                <p>Don't just memorize, understand. Peer tutoring helps both the teacher and the student grasp concepts deeper.</p>
            </div>
        </div>
    </section>

    <section class="about-section-wrapper">
        <div class="about-container-card">
            <div class="about-img">
                <img src="indexpageimage.avif" alt="Students Learning">
            </div>
            <div class="about-text">
                <h2>Empower Your Learning</h2>
                <p>EduMeet isn't just a platform; it's a movement towards collaborative education. We believe that every student has something to teach and something to learn.</p>
                <p>Whether you are struggling with Calculus or you are a master of History, there is a place for you here. Connect, schedule lessons, and track your progress all in one place.</p>
                <a href="register.php" class="btn btn-register">Join the Community</a>
            </div>
        </div>
    </section>

    <footer>
        <a href="#" class="footer-logo">EduMeet</a>
        <div class="footer-links">
            <a href="#">About Us</a>
            <a href="#">Contact</a>
            <a href="#">Privacy Policy</a>
            <a href="#">Terms of Service</a>
        </div>
        <div class="copyright">
            &copy; 2025 EduMeet System. All rights reserved.
        </div>
    </footer>

</body>
</html>