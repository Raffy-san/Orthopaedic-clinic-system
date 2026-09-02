const patients = window.patientMapData || [];

const statusColors = {
    Confirmed: "#10b981", // emerald
    Pending: "#f59e0b",   // amber
    Cancelled: "#f43f5e"  // rose
};

// Buckets which each age falls into, so a single patient can match "all" + their own bucket
function getAgeGroup(age) {
    if (age < 30) return "under30";
    if (age <= 59) return "30to59";
    return "60plus";
}

// Human-readable bracket label for the popup — never the exact age, just the range
function getAgeLabel(age) {
    if (age < 30) return "Below 30";
    if (age <= 59) return "30 - 59";
    return "60 Above";
}

// Format date to "Aug, 30 2026" format
function formatDate(dateStr) {
    const date = new Date(dateStr);
    const monthAbbr = date.toLocaleDateString('en-US', { month: 'short' });
    const day = date.getDate();
    const year = date.getFullYear();
    return `${monthAbbr}, ${day} ${year}`;
}

// Format time to "8:00" format (hours:minutes only)
// Convert 24-hour format to 12-hour format
const convertTo12HourFormat = (time24) => {
    const [hours, minutes] = time24.split(':');
    let hour = parseInt(hours);
    const meridiem = hour >= 12 ? 'PM' : 'AM';
    if (hour > 12) {
        hour -= 12;
    } else if (hour === 0) {
        hour = 12;
    }
    return {
        time12: `${hour}:${minutes}`,
        meridiem: meridiem
    };
};

const map = L.map('patientMap').setView([10.13, 124.85], 10);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Group patients by location (lat, lng)
function groupPatientsByLocation(patients) {
    const locationMap = {};
    
    patients.forEach((p, index) => {
        const key = `${p.lat},${p.lng}`;
        if (!locationMap[key]) {
            locationMap[key] = {
                lat: p.lat,
                lng: p.lng,
                patients: []
            };
        }
        locationMap[key].patients.push({ ...p, originalIndex: index });
    });
    
    return Object.values(locationMap);
}

// Create markers for each unique location
const locations = groupPatientsByLocation(patients);

const markers = locations.map((location) => {
    const marker = L.circleMarker([location.lat, location.lng], {
        radius: 9,
        fillColor: statusColors[location.patients[0].status] || "#64748b",
        color: "#ffffff",
        weight: 2,
        fillOpacity: 0.9
    });

    // Build popup with all patients at this location
    const patientsList = location.patients.map((p, idx) => `
        <div class="patient-item" style="margin-bottom: 10px; padding-bottom: 10px; border-bottom: 1px solid #e5e7eb;">
            <h4 style="margin: 0 0 5px 0; font-weight: 600;">Patient ${p.originalIndex + 1}</h4>
            <p style="margin: 3px 0; font-size: 12px;">(${getAgeLabel(p.age)}, ${p.gender})</p>
            <p style="margin: 3px 0; font-size: 12px;">${p.city}</p>
            <p style="margin: 3px 0; font-size: 12px;">${formatDate(p.date)} - ${convertTo12HourFormat(p.time).time12} ${convertTo12HourFormat(p.time).meridiem}</p>
            <span class="status" style="display: inline-block; background: ${statusColors[p.status]}22; color: ${statusColors[p.status]}; padding: 3px 8px; border-radius: 4px; font-size: 11px; font-weight: 600;">${p.status}</span>
        </div>
    `).join('');

    const popupHtml = `
        <div class="patient-popup" style="max-width: 300px;">
            <h3 style="margin: 0 0 10px 0;">${location.patients.length > 1 ? location.patients.length + ' Patients at this Location' : 'Patient Details'}</h3>
            ${patientsList}
        </div>
    `;
    
    marker.bindPopup(popupHtml);

    // Collect all age groups for patients at this location
    const ageGroups = location.patients.map(p => getAgeGroup(p.age));

    return { marker, ageGroups, location };
});

// All markers visible by default
markers.forEach(m => m.marker.addTo(map));

// Filter button behavior
const filterButtons = document.querySelectorAll('.age-filter-btn');
filterButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const selected = btn.dataset.age;

        // toggle active styling
        filterButtons.forEach(b => b.classList.remove('active-filter'));
        btn.classList.add('active-filter');

        markers.forEach(({ marker, ageGroups }) => {
            // Show marker if selected is "all" OR if any patient at this location matches the age group
            const shouldShow = selected === "all" || ageGroups.includes(selected);
            if (shouldShow) {
                if (!map.hasLayer(marker)) marker.addTo(map);
            } else {
                if (map.hasLayer(marker)) map.removeLayer(marker);
            }
        });
    });
});