<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: homepage.php");
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
  <title>إنشاء حساب - Sadaqa</title>
  <link rel="stylesheet" href="styles-merged.css" /> <!-- styles -->
</head>
<body id="login-page-body">

  <div class="page-wrapper" id="pageWrapper">

    <header class="top-header" id="topHeader">
      <div class="small-logo-box" id="smallLogoBox">
        <img src="../images/Logo.png" alt="شعار صغير" class="small-logo-image" id="smallLogoImage" />
      </div>
    </header>

    <main class="login-main-section" id="loginMainSection">

      <section class="login-card-container" id="loginCardContainer">

        <div class="big-logo-box" id="bigLogoBox">
          <img src="../images/Sadaqa logo.png" alt="شعار Sadaqa" class="big-logo-image" id="bigLogoImage" />
        </div>

        <div class="form-content-box" id="formContentBox">
          <h1 class="main-title" id="mainTitle">إنشاء حساب جديد</h1>
          <p class="sub-title" id="subTitle">كن جزءًا منا وساهم في صناعة الأثر</p>

          <form class="login-form" id="signupForm" action="../php/signup_process.php" method="POST">

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
                minlength="8"
              />
              <span class="tooltip-container">
              <span class="help-icon">i</span>
              <span class="tooltip-text">يجب أن تحتوي كلمة المرور على 8 أحرف أو أرقام، على الأقل.</span>
              </span>
            </div>

            <div class="input-group" id="confirmPasswordGroup">
              <label for="confirmPasswordInput" class="input-label" id="confirmPasswordLabel">تأكيد كلمة المرور</label>
              <input
                type="password"
                name="confirmPassword"
                class="form-input"
                id="confirmPasswordInput"
                placeholder="أعد إدخال كلمة المرور"
                required
              />
            </div>

            <button type="submit" class="login-button" id="loginButton">إنشاء الحساب</button>

            <div class="signup-box" id="signupBox">
              <span class="signup-text" id="signupText">لديك حساب بالفعل؟</span>
              <a href="login.php" class="signup-link" id="signupLink">تسجيل الدخول</a>
            </div>

          </form>
        </div>

      </section>

    </main>
  </div>

  
<script src="main.js"></script>
</body>
</html>



