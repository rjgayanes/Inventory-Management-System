const map = L.map("map").setView([14.5995, 120.9842], 13);

// Load OpenStreetMap tiles
L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    maxZoom: 19,
    attribution: '© OpenStreetMap contributors'
}).addTo(map);

let marker, circle;

// Function to update user location
async function updateLocation(position) {
    const lat = position.coords.latitude;
    const lng = position.coords.longitude;
    const accuracy = position.coords.accuracy;

    // Marker
    if (!marker) {
    marker = L.marker([lat, lng]).addTo(map).bindPopup("You are here").openPopup();
    } else {
    marker.setLatLng([lat, lng]);
    }

    // Circle
    if (!circle) {
    circle = L.circle([lat, lng], {
        color: "blue",
        fillColor: "#3f8efc",
        fillOpacity: 0.3,
        radius: accuracy
    }).addTo(map);
    } else {
    circle.setLatLng([lat, lng]);
    circle.setRadius(accuracy);
    }

    // Center map
    map.setView([lat, lng], 15);

    // Get address (Reverse Geocoding with Nominatim)
    try {
    const response = await fetch(
        `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
    );
    const data = await response.json();

    let address = "Unknown location";
    if (data.address) {
        const barangay = data.address.suburb || data.address.village || data.address.hamlet || "";
        const municipality = data.address.city || data.address.town || data.address.municipality || "";
        const province = data.address.state || "";
        address = [barangay, municipality, province].filter(Boolean).join(", ");
    }

    document.getElementById("location-info").innerText = `📍 Location: ${address}`;
    } catch (err) {
    document.getElementById("location-info").innerText = "📍 Location: Unable to fetch address";
    }
}

// Error handling
function locationError() {
    document.getElementById("location-info").innerText = "📍 Location: Unable to detect";
    Swal.fire({
        icon: 'warning',
        title: 'Location Error',
        text: 'Unable to retrieve your location.',
        confirmButtonColor: '#dc3545'
    });
}

// Watch user position
if (navigator.geolocation) {
    navigator.geolocation.watchPosition(updateLocation, locationError, {
    enableHighAccuracy: true,
    maximumAge: 0,
    timeout: 10000
    });
} else {
    Swal.fire({
        icon: 'error',
        title: 'Geolocation Not Supported',
        text: "Your browser doesn't support geolocation.",
        confirmButtonColor: '#dc3545'
    });
}