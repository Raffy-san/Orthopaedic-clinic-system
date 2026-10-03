<?php
/**
 * Expects these variables to already be set before including this file:
 * $patient (array), $diagnosis (string), $remarks (string),
 * $doctorName (string), $issuedDate (string, formatted), $logoDataUri (string),
 * $consultationDate (string|null), $age (int|null)
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
            line-height: 1.0;
        }

        .header img {
            max-width: 230px;
        }

        .header h1 {
            font-size: 18px;
            margin: 8px 0;
            font-weight: bold;
        }

        .header span {
            font-size: 12px;
            display: block;
            color: #555;
        }

        .header p {
            font-size: 10px;
            margin: 4px 0;
            color: #555;
        }

        .address-table {
            width: auto;
            margin: 8px auto;
            border-collapse: collapse;
        }

        .address-table td {
            vertical-align: top;
            padding: 4px 16px;
        }

        .address-table .left {
            border-right: 1px solid #1e6b34;
            text-align: center;
        }

        .address-table .right {
            text-align: center;
        }

        .address-table h1 {
            font-size: 14px;
            margin: 0 0 2px;
        }

        .address-table p {
            font-size: 10px;
            margin: 2px 0;
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

        .content p span {
            text-decoration: underline;
        }

        .signature-table {
            width: 100%;
            margin-top: 80px;
        }

        .signature-table td {
            vertical-align: top;
        }

        .signature-spacer {
            width: 60%;
        }

        .signature-block {
            text-align: left;
            line-height: 0.4;
        }

        .signature-block h1 {
            margin: 0;
            font-size: 14px;
            font-weight: bold;
        }

        .footer-note {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 10px;
            color: #777;
            text-align: center;
        }

        .footer-note table {
            width: 100%;
            border-collapse: collapse;
        }

        .footer-note td {
            width: 25%;
            white-space: nowrap;
        }

        .footer-note p {
            margin: 0;
        }

        .footer-icon {
            color: #1e6b34;
            font-size: 12px;
            font-weight: bold;
            padding-right: 4px;
        }

        .facebook-icon {
            display: inline-block;
            width: 12px;
            height: 12px;
            line-height: 12px;
            padding-right: 0;
            margin-right: 4px;
            border-radius: 50%;
            background-color: #1877F2;
            color: #fff;
            font-family: Arial, sans-serif;
            font-size: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="header">
        <img src="<?= htmlspecialchars($logoDataUri, ENT_QUOTES, 'UTF-8') ?>" alt="Clinic Logo">
        <h1>DR. THIAM TIU FOOKSON, DPBO, FPOA</h1>
        <span>ORTHOPAEDIC SURGEON</span>
        <p>Fracture - Bone, Muscle, Joint Disease - Diabetic Limb - Sports Injuries</p>

        <table class="address-table">
            <tr>
                <td class="left">
                    <h1>MAASIN</h1>
                    <p>LIVING HOPE HOSPITAL OPD</p>
                    <p>MON-TUES-WED-FRI-SAT</p>
                    <p>9 AM TO 12 NOON</p>
                </td>
                <td class="right">
                    <h1>SOGOD</h1>
                    <p>CORROMPIDO HOSP. OPD</p>
                    <p>THURSDAY</p>
                    <p>9 AM TO 12 NOON</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="title">MEDICAL CERTIFICATE</div>

    <div class="content">
        <p>
            To whom it may concern:
        </p>
        <p>
            This is to certify that Mr./Ms./Mrs. <span>
                <?= htmlspecialchars($patient['FirstName'] . ' ' . $patient['LastName']) ?> </span> age/sex
            <span><?= htmlspecialchars((string) $age) . ' / ' . $patient['Gender'] ?></span> of
            <span><?= htmlspecialchars($patient['Address']) ?></span> was seen and examined on
            <span><?= htmlspecialchars($consultationDate) ?></span>
        </p>
        <p><strong>Diagnosis:</strong> <?= htmlspecialchars($diagnosis) ?></p>
        <?php if (!empty($remarks)): ?>
            <p><strong>Remarks:</strong> <?= htmlspecialchars($remarks) ?></p>
        <?php endif; ?>
        <p>
            This certificate is being issued upon the request of <span>
                <?= htmlspecialchars($patient['FirstName'] . ' ' . $patient['LastName']) ?> </span> for whatever purpose
            it may serve (excluding legal matters).
        </p>
    </div>

    <table class="signature-table">
        <tr>
            <td class="signature-spacer"></td>
            <td class="signature-block">
                <p>Respectfully Yours,</p>
            </td>
        </tr>
        <tr>
            <td class="signature-spacer"></td>
            <td class="signature-block">
                <h1>Thyam T. Fookson, M.D.</h1>
                <p>Lincense No. 011925</p>
                <p>PTR#</p>
            </td>
        </tr>
    </table>

    <footer class="footer-note">
        <table>
            <tr>
                <td><span class="footer-icon">&#9742;</span>CONTACT US:</td>
                <td><span class="footer-icon">&#9742;</span>0956 304 2109</td>
                <td><span class="facebook-icon">f</span>sl.ortho</td>
                <td><span class="footer-icon">&#9993;</span>sl_ortho@yahoo.com</td>
            </tr>
        </table>
    </footer>
</body>

</html>