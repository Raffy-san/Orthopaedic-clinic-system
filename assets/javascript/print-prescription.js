(function () {
    const logoUrl = new URL('../assets/img/logo4.png', window.location.href).href;

    function esc(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function calcAge(birthDate) {
        if (!birthDate) return '';
        const [y, m, d] = String(birthDate).slice(0, 10).split('-').map(Number);
        if (!y || !m || !d) return '';
        const today = new Date();
        let age = today.getFullYear() - y;
        const hadBirthday = today.getMonth() + 1 > m ||
            (today.getMonth() + 1 === m && today.getDate() >= d);
        return hadBirthday ? age : age - 1;
    }

    function formatDate(value) {
        const date = value ? new Date(String(value).replace(' ', 'T')) : new Date();
        const safe = isNaN(date) ? new Date() : date;
        return safe.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
    }

    /**
     * opts = {
     *   patientName, birthDate, gender,
     *   date (optional, defaults to today),
     *   prescriptions: [{ medicine, dosage, frequency, duration, quantity, instructions }]
     * }
     */
    window.printPrescriptionSlip = function (opts) {
        const prescriptions = opts.prescriptions || [];
        if (!prescriptions.length) return;

        const rowsHTML = prescriptions.map(rx => `
            <tr>
                <td>${esc(rx.medicine)}</td>
                <td>${esc(rx.dosage)}</td>
                <td>${esc(rx.frequency)}</td>
                <td>${esc(rx.duration || 'As needed')}</td>
                <td class="center">${esc(rx.quantity ?? '-')}</td>
                <td>${esc(rx.instructions || '-')}</td>
            </tr>
        `).join('');

        const printWindow = window.open('', '', 'height=600,width=800');
        if (!printWindow) {
            alert('Please allow pop-ups to print the prescription.');
            return;
        }

        printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Prescription</title>
            <style>
                body { font-family: Arial, sans-serif; padding:0 10px 10px 10px; background-color: #fff; }
                .header { text-align: center; border-bottom: 2px solid #1e6b34; line-height: 1.0; margin-bottom: 30px; }
                .header img { max-width: 180px; }
                .header h1 { font-size: 18px; margin: 8px 0; font-weight: bold; }
                .header span { font-size: 12px; display: block; color: #555; }
                .header p { font-size: 10px; margin: 4px 0; color: #555; }
                .address-table { width: auto; margin: 8px auto; border-collapse: collapse; }
                .address-table td { vertical-align: top; padding: 4px 16px; }
                .address-table .left { border-right: 1px solid #1e6b34; text-align: center; }
                .address-table .right { text-align: center; }
                .address-table h1 { font-size: 14px; margin: 0 0 2px; }
                .address-table p { font-size: 10px; margin: 2px 0; color: #555; }
                .patient-section { margin-bottom: 25px; }
                .patient-info { font-size: 12px; color: #333; line-height: 1.6; }
                .patient-row { display: flex; gap: 24px; margin-top: 8px; }
                .patient-field { display: flex; align-items: flex-end; gap: 6px; }
                .patient-field .value { border-bottom: 1px solid #333; padding: 0 6px; min-width: 70px; }
                .patient-field.name .value { min-width: 280px; }
                .patient-field.date .value { min-width: 130px; }
                .prescription-section { margin-top: 30px; }
                .prescription-table { width: 100%; border-collapse: collapse; font-size: 12px; }
                .prescription-table th, .prescription-table td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; vertical-align: top; color: #333; }
                .prescription-table th { background-color: #f5f5f5; font-size: 12px; }
                .prescription-table td.center, .prescription-table th.center { text-align: center; }
                .signature-area { position: fixed; bottom: 10px; right: 10px; width: 150px; font-size: 12px; color: #333; }
                .sig-line { display: flex; justify-content: center; gap: 6px; border-bottom: 1px solid #333; margin-top: 4px; min-height: 14px; line-height: 14px; padding-bottom: 1px; font-weight: bold; }
                .sig-line:first-child { margin-top: 28px; }
                .sig-line .sig-name { flex: 1; text-align: center; }
                .sig-label { text-align: center; font-size: 10px; line-height: 12px; margin-top: 2px; }
            </style>
        </head>
        <body>
            <div class="header">
                <img src="${logoUrl}" alt="Clinic Logo">
                <h1>DR. THYAM TIU FOOKSON, DPBO, FPOA</h1>
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

            <div class="patient-section">
                <div class="patient-info">
                    <div class="patient-field name">
                        <strong>Name:</strong>
                        <span class="value">${esc(opts.patientName)}</span>
                    </div>
                    <div class="patient-row">
                        <div class="patient-field"><strong>Age:</strong><span class="value">${esc(calcAge(opts.birthDate))}</span></div>
                        <div class="patient-field"><strong>Sex:</strong><span class="value">${esc(opts.gender || '')}</span></div>
                        <div class="patient-field date"><strong>Date:</strong><span class="value">${esc(formatDate(opts.date))}</span></div>
                    </div>
                </div>
            </div>

            <div class="prescription-section">
                <table class="prescription-table">
                    <thead>
                        <tr>
                            <th>Medicine</th><th>Dosage</th><th>Frequency</th><th>Duration</th>
                            <th class="center">Qty</th><th>Instructions</th>
                        </tr>
                    </thead>
                    <tbody>${rowsHTML}</tbody>
                </table>
            </div>

            <div class="signature-area">
                <div class="sig-line"><span class="sig-name">Thyam T. Fookson</span><span>M.D.</span></div>
                <div class="sig-label">Signature over Printed Name</div>
                <div class="sig-line">011925</div>
                <div class="sig-label">License #</div>
                <div class="sig-line">&nbsp;</div>
                <div class="sig-label">S2 #</div>
                <div class="sig-line">&nbsp;</div>
                <div class="sig-label">PTR #</div>
            </div>
        </body>
        </html>`);
        printWindow.document.close();

        const img = printWindow.document.querySelector('.header img');
        if (img && !img.complete) {
            img.onload = () => printWindow.print();
            img.onerror = () => printWindow.print();
        } else {
            setTimeout(() => printWindow.print(), 250);
        }
    };
})();