<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>New Comment Notification</title>
  <style>
    body {
      font-family: 'Segoe UI', Arial, sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }
    .email-container {
      background-color: #ffffff;
      max-width: 600px;
      margin: 30px auto;
      border-radius: 8px;
      overflow: hidden;
      box-shadow: 0 0 10px rgba(0,0,0,0.05);
    }
    .header {
      background-color: #0f766e;
      padding: 20px;
      text-align: center;
    }
    .header img {
      max-height: 40px;
    }
    .content {
      padding: 30px;
      color: #333333;
    }
    .content h2 {
      margin-top: 0;
      font-size: 20px;
      color: #0f766e;
    }
    .comment-box {
      background-color: #f9fafb;
      border-left: 4px solid #0f766e;
      padding: 15px;
      margin: 20px 0;
      border-radius: 4px;
      color: #111827;
      font-style: italic;
    }
    .footer {
      background-color: #0f766e;
      color: #ffffff;
      text-align: center;
      padding: 20px;
      font-size: 12px;
    }
    .footer .social-icons img {
      width: 20px;
      margin: 0 6px;
      filter: brightness(0) invert(1);
    }
    a.button {
      display: inline-block;
      padding: 10px 20px;
      background-color: #0f766e;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      margin-top: 20px;
    }
  </style>
</head>
<body>
  <div class="email-container">

    <!-- Header -->
    <div class="header">
      <img src="https://res.cloudinary.com/dwi5dlj62/image/upload/v1747243088/logo-bxL4WHcf_utrjxi.png" alt="{{ $data['company_name'] ?? 'Company' }} Logo">
    </div>

    <!-- Content -->
    <div class="content">
      <h2>Hi {{ $data['candidate_name'] }},</h2>

      <p>
        A new comment has been added to your profile by 
        <strong>{{ $data['commenter_name'] }}</strong>
      </p>

      <div class="comment-box">
        "{{ $data['comment_text'] }}"
      </div>

      <p>You can log in to your account to review this and respond if needed.</p>

      @if(isset($data['login_link']))
        <a href="{{ $data['login_link'] }}" class="button">View Comment</a>
      @endif

      <p style="margin-top: 30px;">
        Best regards,<br>
        {{ $data['sender_name'] ?? 'Recruitment Team' }}<br>
        {{ $data['company_name'] ?? 'Company' }}
      </p>
    </div>

    <!-- Footer -->
    <div class="footer">
      <div class="social-icons">
        <a href="#"><img src="https://cdn-icons-png.flaticon.com/512/2111/2111392.png" alt="Facebook" /></a>
        <a href="#"><img src="https://cdn-icons-png.flaticon.com/512/174/174855.png" alt="Instagram" /></a>
        <a href="#"><img src="https://cdn-icons-png.flaticon.com/512/174/174857.png" alt="LinkedIn" /></a>
      </div>
      &copy; {{ date('Y') }} {{ $data['company_name'] ?? 'Your Company' }}. All rights reserved.
    </div>

  </div>
</body>
</html>
