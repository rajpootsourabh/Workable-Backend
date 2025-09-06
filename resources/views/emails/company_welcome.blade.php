<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Welcome to Hustoro</title>
  <style>
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background-color: #000;
      margin: 0;
      padding: 0;
    }
    .email-container {
      background-color: #ffffff;
      max-width: 600px;
      margin: 0 auto;
    }
    .header {
      background-color: #000000;
      text-align: center;
      padding: 20px 0;
    }
    .header img {
      max-height: 45px;
    }
    .content {
      padding: 40px 30px 20px;
      text-align: center;
    }
    .content img.main-icon {
      width: 100px;
      margin-bottom: 20px;
    }
    .content h2 {
      font-size: 20px;
      margin: 0;
      color: #000;
    }
    .content .welcome-text {
      font-size: 15px;
      margin-top: 6px;
      color: #444;
    }
    .divider {
      border-bottom: 2px solid #0f766e;
      width: 100%;
      margin: 20px 0 30px;
    }
    .content p {
      font-size: 14px;
      color: #333;
      text-align: left;
      margin: 10px 0;
    }
    .credentials {
      background-color: #047857;
      color: #ffffff;
      border-radius: 10px;
      padding: 15px;
      font-size: 14px;
      margin: 20px 0;
      text-align: left;
      line-height: 1.7;
    }
    .credentials a {
      color: #fff;
      text-decoration: underline;
    }
    .footer {
      background-color: #000000;
      color: #ffffff;
      text-align: center;
      padding: 30px 10px 20px;
      font-size: 12px;
    }
    .social-icons {
      margin-bottom: 15px;
    }
    .social-icons img {
      width: 24px;
      margin: 0 6px;
      vertical-align: middle;
      filter: brightness(0) invert(1);
    }
    .unsubscribe {
      color: #00e0c7;
      text-decoration: none;
    }
  </style>
</head>
<body>
  <div class="email-container">
    <!-- Header -->
    <div class="header">
      <img src="https://res.cloudinary.com/dwi5dlj62/image/upload/v1756741894/hustoro_logo_white_dxorts.png" alt="Hustoro Logo" />
    </div>

    <!-- Main Content -->
    <div class="content">
      <img class="main-icon" src="https://cdn-icons-png.flaticon.com/512/10373/10373129.png" alt="Envelope Icon" />
      <h2>Welcome to Hustoro, {{ $data['company_name'] }}!</h2>
      <div class="welcome-text">
        We're excited to have your company join our platform.
      </div>
      <div class="divider"></div>

      <p>Your company profile has been successfully created. Below are your login credentials:</p>

      <div class="credentials">
        🌐 <strong>Website:</strong> <a href="https://hustoro.com/login">https://hustoro.com/login</a><br/>
        🏢 <strong>Company Name:</strong> {{ $data['company_name'] }}<br/>
        📧 <strong>Email:</strong> {{ $data['email'] }}<br/>
        🔐 <strong>Password:</strong> {{ $data['password'] }}<br/>
        🧑‍💼 <strong>Role:</strong> {{ $data['role'] }}
      </div>

      <p>Please log in using the above credentials and change your password immediately for security.</p>

      <p>If you have any questions or need assistance, reach out to us at <a href="mailto:support@hustoro.com">support@hustoro.com</a>.</p>

      <p style="margin-top: 30px;">
        Warm regards,<br/>
        Team Hustoro
      </p>
    </div>

    <!-- Footer -->
    <div class="footer">
      <div class="social-icons">
        <a href="#"><img src="https://cdn-icons-png.flaticon.com/512/2111/2111392.png" alt="Facebook" /></a>
        <a href="#"><img src="https://cdn-icons-png.flaticon.com/512/174/174855.png" alt="Instagram" /></a>
        <a href="#"><img src="https://cdn-icons-png.flaticon.com/512/174/174857.png" alt="LinkedIn" /></a>
        <a href="#"><img src="https://cdn-icons-png.flaticon.com/512/733/733579.png" alt="Twitter" /></a>
      </div>
      <p>©2025 www.hustoro.com. All Rights Reserved.</p>
      <a href="#" class="unsubscribe">Unsubscribe</a>
    </div>
  </div>
</body>
</html>
