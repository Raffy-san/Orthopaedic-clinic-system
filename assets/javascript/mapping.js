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

const map = L.map('patientMap').setView([10.13, 124.85], 10);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 18,
    attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// Keep a reference to each marker alongside its patient data so we can filter later
const markers = patients.map((p, index) => {
    const marker = L.circleMarker([p.lat, p.lng], {
        radius: 9,
        fillColor: statusColors[p.status] || "#64748b",
        color: "#ffffff",
        weight: 2,
        fillOpacity: 0.9
    });

    const popupHtml = `
            <div class="patient-popup">
                <h3>Patient ${index + 1}</h3>
                <p>(${getAgeLabel(p.age)}, ${p.gender})</p>
                <p>${p.city}</p>
                <span class="status" style="background:${statusColors[p.status]}22; color:${statusColors[p.status]};">${p.status}</span>
            </div>
        `;
    marker.bindPopup(popupHtml);

    return { marker, ageGroup: getAgeGroup(p.age) };
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

        markers.forEach(({ marker, ageGroup }) => {
            const shouldShow = selected === "all" || ageGroup === selected;
            if (shouldShow) {
                if (!map.hasLayer(marker)) marker.addTo(map);
            } else {
                if (map.hasLayer(marker)) map.removeLayer(marker);
            }
        });
    });
});