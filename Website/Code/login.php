<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: homepage.php");
    exit();
}
if (isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
  <link rel="icon" href="../images/Logo.png" type="image/png">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <title>تسجيل الدخول - Sadaqa</title>
  <link rel="stylesheet" href="styles-merged.css" /> <!-- styles -->
</head>
<body id="login-page-body">

  <div class="page-wrapper" id="pageWrapper">

    <!-- الشعار الصغير فوق اليمين -->
    <header class="top-header" id="topHeader">
      <div class="small-logo-box" id="smallLogoBox">
        <img src="../images/Logo.png" alt="شعار صغير" class="small-logo-image" id="smallLogoImage" />
      </div>
    </header>

    <!-- محتوى الصفحة -->
    <main class="login-main-section" id="loginMainSection">

      <section class="login-card-container" id="loginCardContainer">

        <!-- الشعار الكبير بالنص -->
        <div class="big-logo-box" id="bigLogoBox">
          <img src="../images/Sadaqa logo.png" alt="شعار Sadaqa" class="big-logo-image" id="bigLogoImage" />
        </div>

        <div class="form-content-box" id="formContentBox">
          <h1 class="main-title" id="mainTitle">تسجيل الدخول</h1>
          <p class="sub-title" id="subTitle">أدخل بياناتك للوصول إلى حسابك</p>

          <form class="login-form" id="loginForm" action="../php/login_process.php" method="POST">

            <div class="input-group" id="emailGroup">
              <label for="emailInput" class="input-label" id="emailLabel">البريد الإلكتروني</label>
              <input
                type="email"
                name="email"
                class="form-input"
                id="emailInput"
                placeholder="أدخل بريدك الإلكتروني"
                required
              />
            </div>

            <div class="input-group" id="passwordGroup">
              <label for="passwordInput" class="input-label" id="passwordLabel">كلمة المرور</label>
              <input
                type="password"
                name="password"
                class="form-input"
                id="passwordInput"
                placeholder="أدخل كلمة المرور"
                required
              />
            </div>

            <button type="submit" class="login-button" id="loginButton">دخول</button>

            <div class="signup-box" id="signupBox">
              <span class="signup-text" id="signupText">ليس لديك حساب؟</span>
              <a href="signup.php" class="signup-link" id="signupLink">إنشاء حساب</a>
            </div>

          </form>
        </div>

      </section>

    </main>
  </div>

  <script src="main.js"></script>
</body>
</html>



