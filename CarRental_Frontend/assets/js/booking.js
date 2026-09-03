document.addEventListener('DOMContentLoaded', function () {
    const carIdInput = document.getElementById('CarID');
    const startInput = document.getElementById('StartDate');
    const endInput = document.getElementById('EndDate');
    const msgBox = document.getElementById('availabilityMessage');
    const rentalDaysPreview = document.getElementById('rentalDaysPreview');
    const totalPricePreview = document.getElementById('totalPricePreview');
    const bookingForm = document.getElementById('bookingForm');
    const calendarList = document.getElementById('calendarList');

    if (!carIdInput) return;

    const carId = carIdInput.value;
    const pricePerDay = parseFloat(carIdInput.dataset.pricePerDay || '0');

    window.changeMainImage = function (src) {
        const main = document.getElementById('mainPreview');
        if (main) main.src = src;
    };

    function formatMoney(number) {
        return Number(number).toLocaleString('vi-VN') + ' VNĐ';
    }

    function calculatePreview() {
        const start = startInput?.value;
        const end = endInput?.value;

        if (!start || !end) {
            if (rentalDaysPreview) rentalDaysPreview.textContent = '0 ngày';
            if (totalPricePreview) totalPricePreview.textContent = '0 VNĐ';
            return;
        }

        const startDate = new Date(start);
        const endDate = new Date(end);
        const diff = endDate - startDate;
        let days = Math.ceil(diff / (1000 * 60 * 60 * 24));

        if (days < 1 || isNaN(days)) days = 0;

        if (rentalDaysPreview) rentalDaysPreview.textContent = days + ' ngày';
        if (totalPricePreview) totalPricePreview.textContent = formatMoney(days * pricePerDay);
    }

    async function checkAvailability() {
        const start = startInput?.value;
        const end = endInput?.value;

        calculatePreview();

        if (!carId || !start || !end) {
            if (msgBox) {
                msgBox.className = 'availability-box';
                msgBox.textContent = '';
            }
            return;
        }

        try {
            const url = `/Carrental/CarRental_Backend/api/bookings/check_availability.php?car_id=${carId}&start_date=${encodeURIComponent(start)}&end_date=${encodeURIComponent(end)}`;
            const res = await fetch(url);
            const data = await res.json();

            if (msgBox) {
                msgBox.textContent = data.message || '';
                msgBox.className = data.available ? 'availability-box success' : 'availability-box error';
            }
        } catch (e) {
            if (msgBox) {
                msgBox.textContent = 'Không thể kiểm tra lịch xe lúc này.';
                msgBox.className = 'availability-box error';
            }
        }
    }

    async function loadCalendar() {
        if (!calendarList) return;

        try {
            const res = await fetch(`/Carrental/CarRental_Backend/api/bookings/calendar.php?car_id=${carId}`);
            const data = await res.json();

            calendarList.innerHTML = '';

            if (!data.length) {
                calendarList.innerHTML = '<li>Xe hiện chưa có lịch bận.</li>';
                return;
            }

            data.forEach(item => {
                const li = document.createElement('li');
                li.textContent = `${item.StartDate} → ${item.EndDate} (${item.Status})`;
                calendarList.appendChild(li);
            });
        } catch (e) {
            calendarList.innerHTML = '<li>Không tải được lịch bận.</li>';
        }
    }

    startInput?.addEventListener('change', checkAvailability);
    endInput?.addEventListener('change', checkAvailability);

    bookingForm?.addEventListener('submit', async function (e) {
        const start = startInput?.value;
        const end = endInput?.value;

        if (!start || !end) {
            e.preventDefault();
            alert('Vui lòng chọn ngày nhận và ngày trả xe.');
            return;
        }

        const res = await fetch(`/Carrental/CarRental_Backend/api/bookings/check_availability.php?car_id=${carId}&start_date=${encodeURIComponent(start)}&end_date=${encodeURIComponent(end)}`);
        const data = await res.json();

        if (!data.available) {
            e.preventDefault();
            alert(data.message || 'Xe không còn trống trong thời gian này.');
        }
    });

    loadCalendar();
    calculatePreview();
});