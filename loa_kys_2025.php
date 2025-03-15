<?php
// date
$date = date('F d, Y');
$path = "https://storage.ybbfoundation.com/logo/KYS.png";
$type = pathinfo($path, PATHINFO_EXTENSION);
$data = file_get_contents($path);
$logo = 'data:image/' . $type . ';base64,' . base64_encode($data);

$path_sign = "https://storage.ybbfoundation.com/document_invitation/3/1.png";
$type_sign = pathinfo($path_sign, PATHINFO_EXTENSION);
$data_sign = file_get_contents($path_sign);
$img_sign = 'data:image/' . $type_sign . ';base64,' . base64_encode($data_sign);
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Letter of Acceptance and Invitation Letter</title>
  <style>
    body {
      font-family: 'Arial', sans-serif;
      font-size: 12px;
      width: 100%;
      min-height: 100%;
      margin-right: 20px;
      padding: 2%;
    }

    .header {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      /* Changed to three equal columns */
      gap: 20px;
      align-items: center;
      margin-bottom: 12%;
      padding: 5px;
    }

    .logo {
      float: left;
      width: 20%;
    }

    .event-info {
      /* Center the middle column */
      text-align: start;
      /* Center the text within */
      float: left;
    }

    .contact-info {
      justify-self: end;
      /* Align contact info to end of last column */
      text-align: right;
      float: right;
      width: 30%;
      line-height: 1.1;
    }

    .new-tab {
      text-indent: 20px;
      text-align: justify;
    }

    .title {
      font-size: 16px;
      margin: 10px 0;
      font-weight: bold;
      text-align: center;
    }

    .content {
      margin-top: 10px;
      line-height: 1.5;
      padding: 5px;
      text-align: justify;
      text-justify: inter-word;
    }

    .content ul {
      padding-left: 20px;
    }

    .content ul li {
      margin: 5px 0;
    }

    .date-section {
      display: flex;
      justify-content: flex-end;
      margin: 20px 0;
    }

    .signature {
      margin-top: 10px;
      padding: 5px;
      display: flex;
      line-height: 1.3;
      flex-direction: column;
      gap: 5px;
    }

    .footer {
      margin-top: 10px;
      font-size: 11px;
      line-height: 1.3;
      padding: 5px;
      text-align: center;
    }

    a {
      color: #000;
      text-decoration: none;
    }

    a:hover {
      text-decoration: underline;
    }

    .parent {
      position: relative;
      top: 10;
      bottom: 30;
      left: 0;
    }

    .image1 {
      position: relative;
      top: 0;
      left: 0;
    }

    .image2 {
      position: absolute;
      top: 20px;
      left: 20px;
    }
  </style>
</head>

<body>
  <!-- Header -->
  <header class="header">
    <div class="logo">
      <img style="width: 100px; height: auto;"
        src="<?= $logo ?>"
        alt="Youth Academic Forum Logo" />
    </div>
    <div class="event-info">
      <p class="title"><strong>Korea Youth Summit 2025</strong></p>
      <p><strong>Living Culture, Lasting Legacy</strong></p>
    </div>

    <div class="contact-info">
      <p>
        <a href="http://www.youthacademicforum.com">www.koreayouthsummit.com</a>
      </p>
      <p>koreayouthsummit@gmail.com</p>
      <p>+62 851-7338-6622 (YBB Admin)</p>
    </div>

  </header>

  <hr />

  </br>

  <!-- Title -->
  <h1 class="title">Letter of Acceptance and Invitation Letter</h1>

  <!-- Date -->
  <div style="text-align: right;">
    <p>
      <?= $date ?>
    </p>
  </div>

  <!-- Content -->
  <div class="content">
    <p>Dear Mr/Ms,</p>
    <p class="new-tab">
      We are thrilled to acknowledge your interest in joining the <strong>Korea Youth Summit (KYS) 2025</strong>, which will take place in Seoul, South Korea, from <strong>June 30 - July 3, 2025</strong>.
    </p>
    <p class="new-tab">
      We are delighted to formally congratulate you on your acceptance as a participant in this prestigious event. Your details are as follows:
    </p>
    <ul>
      <li>
        <strong>Name :</strong>
        <?= $name ?>
      </li>
      <li>
        <strong>University/Institution:</strong>
        <?= $institution ?>
      </li>
    </ul>

    <p class="new-tab">
      The Korea Youth Summit offers a unique platform to connect with leading experts, engage in thought-provoking discussions, and expand your knowledge on global issues. We encourage you to make the most of this opportunity to positively impact your personal growth and your community.
    </p>

    <p class="new-tab">
      We look forward to welcoming you to Korea and witnessing your contributions to this remarkable gathering of young leaders from around the world. Please do not hesitate to reach out if you require any further documentation to assist with your visa application or permission process.
    </p>

    <p class="new-tab">
      Thank you for your attention, and we are excited to meet you at the summit!
    </p>
  </div>

  <!-- Signature -->
  <div class="signature">
    <p>Sincerely,</p>

    <div class="parent">
      <img class="image1" src="<?= $logo ?>" style="opacity: 0.5; width: 150px; height: auto;" alt="">
      <img class="image2" src="<?= $img_sign ?>" alt="" width="100px;">
    </div>
    <br>
    <p>
      <strong>Muhammad Aldi Subakti</strong>
      </br>
      <span>Chairman of Korea Youth Summit</span>
    </p>

  </div>



  <!-- Footer -->
  <footer class="footer">
    <p>
      <strong>Korea Youth Summit 2025</strong></br>
      Organized by <strong>Youth Break the Boundaries Foundation</strong><br />
      <strong><a href="http://www.koreayouthsummit.com">www.koreayouthsummit.com</a> |
        <a href="mailto:koreayouthsummit@gmail.com">koreayouthsummit@gmail.com</a> |
        +62 851-7338-6622</strong> (YBB Admin)
    </p>
  </footer>

</body>

</html>