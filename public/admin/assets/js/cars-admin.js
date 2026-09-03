document.addEventListener("DOMContentLoaded", function () {
    const searchInput = document.getElementById("searchInput");
    const statusFilter = document.getElementById("statusFilter");
    const table = document.getElementById("carsTable");

    if (!table) return;

    const rows = table.querySelectorAll("tbody tr");

    function filterRows() {
        const keyword = searchInput.value.toLowerCase().trim();
        const status = statusFilter.value.toLowerCase();

        rows.forEach(row => {
            const rowText = row.innerText.toLowerCase();
            const badge = row.querySelector(".badge-custom");
            const rowStatus = badge ? badge.innerText.toLowerCase() : "";

            const matchKeyword = rowText.includes(keyword);
            const matchStatus = status === "" || rowStatus === status;

            row.style.display = (matchKeyword && matchStatus) ? "" : "none";
        });
    }

    if (searchInput) searchInput.addEventListener("keyup", filterRows);
    if (statusFilter) statusFilter.addEventListener("change", filterRows);
});

document.addEventListener("DOMContentLoaded", function () {
    const imageInput = document.getElementById('Images');
    const previewBox = document.getElementById('preview-box');

    if (!imageInput || !previewBox) return;

    imageInput.addEventListener('change', function () {
        previewBox.innerHTML = '';
        const files = Array.from(this.files || []);

        files.forEach(file => {
            if (!file.type.startsWith('image/')) return;

            const reader = new FileReader();
            reader.onload = function (e) {
                const div = document.createElement('div');
                div.className = 'preview-item';
                div.innerHTML = `<img src="${e.target.result}" alt="preview">`;
                previewBox.appendChild(div);
            };
            reader.readAsDataURL(file);
        });
    });
});