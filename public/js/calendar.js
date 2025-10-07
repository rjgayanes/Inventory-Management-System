const monthYear = document.getElementById("month-year");
const calendarDays = document.getElementById("calendar-days");
const prevBtn = document.getElementById("prev");
const nextBtn = document.getElementById("next");

let date = new Date();

function renderCalendar() {
    const year = date.getFullYear();
    const month = date.getMonth();

    monthYear.innerText = date.toLocaleString("default", { month: "long" }) + " " + year;

    const firstDay = new Date(year, month, 1).getDay();
    const lastDate = new Date(year, month + 1, 0).getDate();
    const prevLastDate = new Date(year, month, 0).getDate();

    let days = "";

    // Previous month days
    for (let x = firstDay; x > 0; x--) {
        days += `<div style="color:#bbb">${prevLastDate - x + 1}</div>`;
    }

    // Current month days
    for (let i = 1; i <= lastDate; i++) {
        let todayClass = "";
        let today = new Date();
        if (i === today.getDate() && month === today.getMonth() && year === today.getFullYear()) {
            todayClass = "today";
        }
        days += `<div class="day ${todayClass}">${i}</div>`;
    }

    calendarDays.innerHTML = days;
}

prevBtn.addEventListener("click", () => {
    date.setMonth(date.getMonth() - 1);
    renderCalendar();
});
nextBtn.addEventListener("click", () => {
    date.setMonth(date.getMonth() + 1);
    renderCalendar();
});

renderCalendar();