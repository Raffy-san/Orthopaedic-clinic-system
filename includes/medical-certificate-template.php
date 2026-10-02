<?php
/**
 * Expects these variables to already be set before including this file:
 * $patient (array), $diagnosis (string), $remarks (string),
 * $doctorName (string), $issuedDate (string, formatted)
 */
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 13px;
            color: #1a1a1a;
            margin: 40px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e6b34;
            padding-bottom: 12px;
            margin-bottom: 24px;
        }
        .header h1 {
            font-size: 18px;
            margin: 0;
            color: #1e6b34;
        }
        .header p {
            font-size: 11px;
            margin: 2px 0 0;
            color: #555;
        }
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin: 24px 0;
        }
        .content p {
            line-height: 1.8;
            text-align: justify;
        }
        .field-table {
            width: 100%;
            margin-bottom: 16px;
        }
        .field-table td {
            padding: 4px 0;
            font-size: 13px;
        }
        .field-label {
            width: 140px;
            color: #555;
        }
        .signature-block {
            margin-top: 60px;
            width: 260px;
            float: right;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #1a1a1a;
            margin-bottom: 4px;
        }
        .footer-note {
            margin-top: 100px;
            font-size: 10px;
            color: #777;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>SOUTHERN LEYTE ORTHOPAEDIC CLINIC SYSTEM</h1>
        <p>Maasin City, Southern Leyte</p>
    </div>

    <div class="title">MEDICAL CERTIFICATE</div>

    <table class="field-table">
        <tr>
            <td class="field-label">Patient Name:</td>
            <td><?= htmlspecialchars($patient['FirstName'] . ' ' . $patient['LastName']) ?></td>
        </tr>
        <tr>
            <td class="field-label">Patient Code:</td>
            <td><?= htmlspecialchars($patient['PatientCode']) ?></td>
        </tr>
        <tr>
            <td class="field-label">Date of Birth:</td>
            <td><?= htmlspecialchars($patient['BirthDate']) ?></td>
        </tr>
        <tr>
            <td class="field-label">Date Issued:</td>
            <td><?= htmlspecialchars($issuedDate) ?></td>
        </tr>
    </table>

    <div class="content">
        <p>
            This is to certify that the above-named patient was seen and examined at this clinic
            and was found to have the following condition:
        </p>
        <p><strong>Diagnosis:</strong> <?= htmlspecialchars($diagnosis) ?></p>
        <?php if (!empty($remarks)): ?>
            <p><strong>Remarks:</strong> <?= htmlspecialchars($remarks) ?></p>
        <?php endif; ?>
        <p>
            This certificate is issued upon the patient's request for whatever legal purpose it may serve.
        </p>
    </div>

    <div class="signature-block">
        <div class="signature-line"></div>
        <p><?= htmlspecialchars($doctorName) ?><br>Attending Physician</p>
    </div>

    <div class="footer-note">
        This document was electronically generated and is valid without a signature when issued through the clinic's official system.
    </div>
</body>
</html>