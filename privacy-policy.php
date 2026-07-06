<?php
// privacy-policy.php
// Makueni County ICT Centers — Data Protection & Privacy Notice
include 'includes/db.php';
require 'includes/header.php';
include 'includes/navbar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Protection &amp; Privacy Policy | Makueni County ICT Centers</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .policy-section { margin-bottom: 2rem; }
        .policy-section h2 { font-size: 1.3rem; font-weight: 700; margin-bottom: .75rem; }
        .policy-updated { color: #6c757d; font-size: .9rem; }
    </style>
</head>
<body>

<div class="container py-5" style="max-width: 860px;">

    <h1 class="mb-2">Data Protection &amp; Privacy Policy</h1>
    <p class="policy-updated">Last updated: <?php echo date("F j, Y"); ?></p>
    <hr class="mb-4">

    <div class="policy-section">
        <h2>1. Who We Are</h2>
        <p>
            Makueni County ICT Centers ("the Platform") is a digital training and youth
            empowerment initiative developed and managed by CLOUDP TECH. This notice explains
            what personal data we collect from students and visitors, why we collect it, how
            it is used, and your rights under the Kenya Data Protection Act, 2019.
        </p>
    </div>

    <div class="policy-section">
        <h2>2. What Data We Collect</h2>
        <p>When you register or use this Platform, we may collect:</p>
        <ul>
            <li>Full name and username</li>
            <li>Phone number and email address</li>
            <li>Sub-county / ward, and nearest ICT center selection</li>
            <li>Username and password (password is stored as a secure hash, never in plain text)</li>
            <li>Registration fee payment details processed via M-Pesa (transaction reference,
                phone number, and amount — we do not store your M-Pesa PIN or full financial account details)</li>
            <li>Training progress, certificates, and center ratings/reviews you submit</li>
            <li>Technical data such as login timestamps and basic device/browser information, for security purposes</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>3. Why We Collect It (Purpose &amp; Legal Basis)</h2>
        <p>We process your data because it is necessary to:</p>
        <ul>
            <li>Create and manage your student account and training records</li>
            <li>Place you at an ICT center and generate your training letter and certificates</li>
            <li>Process your Ksh. 100 registration fee via M-Pesa</li>
            <li>Communicate with you about your training, center, or account</li>
            <li>Improve our services using feedback and center ratings</li>
            <li>Comply with legal and reporting obligations to Makueni County Government</li>
        </ul>
        <p>
            Our legal basis for this processing is your <strong>consent</strong> at the point
            of registration, and, where applicable, the performance of the training service
            you have requested from us.
        </p>
    </div>

    <div class="policy-section">
        <h2>4. Who We Share Data With</h2>
        <p>We do not sell your data. We may share limited data with:</p>
        <ul>
            <li>Your assigned ICT center, for placement and training delivery</li>
            <li>Safaricom / M-Pesa Daraja API, solely to process the registration fee payment</li>
            <li>Makueni County Government, for reporting and partnership oversight of the programme</li>
            <li>Service providers who host or maintain the Platform (e.g. our hosting provider),
                strictly to keep the system running</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>5. How Long We Keep Your Data</h2>
        <p>
            We retain student data for as long as your account is active and for a reasonable
            period afterward to issue certificates, respond to disputes, and meet reporting
            obligations. You may request deletion of your account as described in Section 7.
        </p>
    </div>

    <div class="policy-section">
        <h2>6. How We Protect Your Data</h2>
        <ul>
            <li>Passwords are hashed and never stored or displayed in plain text</li>
            <li>Database queries use prepared statements to prevent injection attacks</li>
            <li>Access to student records is restricted to authorised administrators</li>
        </ul>
    </div>

    <div class="policy-section">
        <h2>7. Your Rights</h2>
        <p>Under the Data Protection Act, 2019, you have the right to:</p>
        <ul>
            <li>Be informed of how your data is used (this notice)</li>
            <li>Access the personal data we hold about you</li>
            <li>Request correction of inaccurate data</li>
            <li>Request deletion of your data, subject to legal/record-keeping requirements</li>
            <li>Withdraw consent at any time (this may limit your ability to use the Platform)</li>
            <li>Lodge a complaint with the Office of the Data Protection Commissioner (ODPC), Kenya</li>
        </ul>
        <p>
            To exercise any of these rights, contact us using the details in Section 9.
        </p>
    </div>

    <div class="policy-section">
        <h2>8. Children and Minors</h2>
        <p>
            Some students registering for training may be minors. Where a registrant is under
            18, we recommend registration be done with the involvement or awareness of a
            parent/guardian, and we limit data collected from minors to what is strictly
            necessary for training placement.
        </p>
    </div>

    <div class="policy-section">
        <h2>9. Contact Us</h2>
        <p>
            Questions about this policy or your data can be sent to:<br>
            <strong>CLOUDP TECH</strong><br>
            Email: <a href="mailto:info.cloudptech@gmail.com">info.cloudptech@gmail.com</a>
        </p>
    </div>

    <a href="index.php" class="btn btn-outline-secondary mt-3">&larr; Back to Home</a>

</div>
<?php
include 'includes/footer.php'
?>
</body>
</html>
