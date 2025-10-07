function updateDateTime() { 
    const now = new Date();

    // Format date: Sunday, August 03, 2025
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: '2-digit' };
    const formattedDate = now.toLocaleDateString('en-US', dateOptions);

    // Format time: 09:01:20 P.M.
    let hours = now.getHours();
    const minutes = now.getMinutes().toString().padStart(2, '0');
    const seconds = now.getSeconds().toString().padStart(2, '0');
    const ampm = hours >= 12 ? 'P.M.' : 'A.M.';
    hours = hours % 12;
    hours = hours ? hours : 12; // 0 becomes 12
    const formattedTime = `${hours.toString().padStart(2, '0')}:${minutes}:${seconds} ${ampm}`;

    // Add icons
    document.getElementById('date').innerHTML = `<i class="fa-regular fa-calendar-days"></i> ${formattedDate}`;
    document.getElementById('time').innerHTML = `<i class="fa-regular fa-clock"></i> ${formattedTime}`;
}

updateDateTime();
setInterval(updateDateTime, 1000);