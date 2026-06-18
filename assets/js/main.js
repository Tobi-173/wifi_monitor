const modal = document.getElementById('dataModal');
let isAddMode = false;

// Lưu vị trí WiFi vào LocalStorage thay vì SQL
function saveWiFiToLocal() {
    const locationId = document.getElementById('locationIdInput').value;
    const selectedCells = Array.from(document.querySelectorAll('.grid-item.has-ap'))
                               .map(item => item.getAttribute('data-id'));
    
    localStorage.setItem(`wifi_positions_${locationId}`, JSON.stringify(selectedCells));
}

// Tải vị trí WiFi từ LocalStorage khi load trang
function loadWiFiFromLocal() {
    const locationId = document.getElementById('locationIdInput').value;
    const savedData = localStorage.getItem(`wifi_positions_${locationId}`);
    
    if (savedData) {
        const cellIds = JSON.parse(savedData);
        cellIds.forEach(id => {
            const item = document.querySelector(`.grid-item[data-id='${id}']`);
            if (item) item.classList.add('has-ap');
        });
    }
}

function handleGridClick(id) {
    if (isAddMode) {
        const gridItem = document.querySelector(`.grid-item[data-id='${id}']`);
        gridItem.classList.toggle('has-ap');
        saveWiFiToLocal(); // Tự động lưu mỗi khi click
    } else {
        openModal(id);
    }
}

function openModal(id) {
    document.getElementById('modalCellId').innerText = id;
    document.getElementById('cellIdInput').value = id;

    // Thiết lập thời gian mặc định là hiện tại cho ô nhập liệu (Date Picker)
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    const timeInput = document.getElementById('checkTime');
    if (timeInput) timeInput.value = localDateTime;

    modal.style.display = 'block';
}

function closeModal() {
    modal.style.display = 'none';
    document.getElementById('wifiForm').reset();
}

document.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const sidebarCollapse = document.getElementById('sidebarCollapse');
    const btnToggleAddMode = document.getElementById('btnToggleAddMode');
    const gridContainer = document.querySelector('.grid-container');
    const addStatus = document.getElementById('addModeStatus');

    // Load dữ liệu cũ khi vừa mở trang
    loadWiFiFromLocal();

    // Toggle Chế độ thêm WiFi
    btnToggleAddMode.addEventListener('click', function() {
        isAddMode = !isAddMode;
        this.classList.toggle('active');
        this.innerHTML = isAddMode ? '<i class="ph ph-x-circle"></i> Thoát Chế độ thêm' : '<i class="ph ph-plus-circle"></i> Thêm WiFi';
        addStatus.style.display = isAddMode ? 'inline-block' : 'none';
        gridContainer.classList.toggle('add-mode', isAddMode);
    });

    // Toggle Sidebar
    sidebarCollapse.addEventListener('click', function () {
        sidebar.classList.toggle('active');
    });

    // Xử lý đóng mở menu đa cấp
    const menuToggles = document.querySelectorAll('.menu-toggle');
    menuToggles.forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            e.preventDefault(); // Ngăn load lại trang khi click vào Nhà máy/Xưởng
            const parentLi = this.parentElement;
            
            // Toggle class 'open' cho phần tử li trực tiếp chứa menu này
            parentLi.classList.toggle('open');
            
            // Ngăn sự kiện click lan ra ngoài
            e.stopPropagation();
        });
    });

    // Xử lý Submit Form
    document.getElementById('wifiForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const cellId = document.getElementById('cellIdInput').value;
        const locId = document.getElementById('locationIdInput').value;
        const min = document.getElementById('minSpeed').value;
        const max = document.getElementById('maxSpeed').value;
        const checkTime = document.getElementById('checkTime').value;

        // Tạo FormData để gửi về server
        const formData = new FormData();
        formData.append('location_id', locId);
        formData.append('cell_id', cellId);
        formData.append('min_speed', min);
        formData.append('max_speed', max);
        formData.append('check_time', checkTime);

        // Gửi AJAX tới api.php
        fetch('includes/api.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                // Cập nhật giao diện sau khi server phản hồi thành công
                const infoDiv = document.getElementById(`info-${cellId}`);
                const displayTime = checkTime ? checkTime.replace('T', ' ') : '';
                const formattedTime = new Date(checkTime).toLocaleString('vi-VN');

                infoDiv.innerHTML = `
                    <div style="font-size: 0.75rem; line-height: 1.2;">
                        <span>${displayTime}</span><br>
                        <i class="ph ph-wifi-high"></i><br>
                        <strong>${min}-${max}</strong> ms
                    </div>
                    <div class="cell-tooltip">
                        <strong>Thời gian:</strong> ${formattedTime}<br>
                        <strong>Độ trễ tối thiểu:</strong> ${min} ms<br>
                        <strong>Độ trễ tối đa:</strong> ${max} ms
                    </div>`;

                const gridItem = document.querySelector(`.grid-item[data-id='${cellId}']`);
                
                // Kiểm tra điều kiện độ trễ để đổi màu ngay lập tức
                const isGood = parseFloat(max) < 20;
                gridItem.style.background = isGood ? '#e8f8f5' : '#fdedec';
                gridItem.style.borderColor = isGood ? '#2ecc71' : '#e74c3c';

                closeModal();
            } else {
                alert('Lỗi: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Không thể kết nối tới server để lưu dữ liệu.');
        });
    });
});