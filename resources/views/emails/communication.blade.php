<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>{{ $data['subject'] ?? 'Communication Notification' }}</title>
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
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
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

    .message {
      background-color: #f9fafb;
      border-left: 4px solid #0f766e;
      padding: 15px 20px;
      /* better left-right spacing */
      margin: 20px 0;
      border-radius: 4px;
      color: #111827;
      line-height: 1.6;
      white-space: pre-line;
      min-height: 80px;
      /* keeps size consistent */
      display: block;
      /* remove flex centering */
      text-align: left;
      /* align text neatly */
    }

    .auto-note {
      font-size: 12px;
      color: #666;
      margin-top: 20px;
      text-align: center;
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
  </style>
</head>

<body>
  <div class="email-container">

    <!-- Header -->
    <div class="header">
      <img src="https://res.cloudinary.com/dwi5dlj62/image/upload/v1756741894/hustoro_logo_white_dxorts.png" alt="{{ $data['company_name'] ?? 'Company' }} Logo">
    </div>

    <!-- Content -->
    <div class="content">

      <div class="message">
        {!! nl2br(e($data['message'])) !!}
      </div>

      <div class="auto-note">
        ⚠️ This is an automated email. Please do not reply.
      </div>
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