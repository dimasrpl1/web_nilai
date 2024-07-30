document.getElementById('peran').addEventListener('change', function() {
    var peran = this.value;
    var popup = document.getElementById('popup');
    var popupContent = document.getElementById('popup-content');
    popupContent.innerHTML = ''; // Clear previous content

    if (peran === 'guru') {
        popupContent.innerHTML = `
            <label for="mapel">Mata Pelajaran:</label><br>
            <select id="mapel" name="mapel" required>
                <option value="">Pilih Mata Pelajaran</option>
                <option value="Produktif">Produktif</option>
                <option value="PPKN">PPKN</option>
                <option value="PAI">PAI</option>
                <option value="Matematika">Matematika</option>
                <option value="Bahasa Inggris">Bahasa Inggris</option>
                <option value="PKK">PKK</option>
                <option value="MPKK">MPKK</option>
                <option value="Bahasa Indonesia">Bahasa Indonesia</option>
            </select><br><br>
        `;
    } else if (peran === 'siswa') {
        popupContent.innerHTML = `
            <label for="kelas">Kelas:</label><br>
            <select id="kelas" name="kelas" required>
                <option value="">Pilih Kelas</option>
                <option value="XII RPL 1">XII RPL 1</option>
                <option value="XII RPL 2">XII RPL 2</option>
            </select><br><br>
        `;
    } else if (peran === 'orangtua') {
        popupContent.innerHTML = `
            <label for="kelas">Kelas Anak:</label><br>
            <select id="kelas" name="kelas" required onchange="loadNamaAnak()">
                <option value="">Pilih Kelas</option>
                <option value="XII RPL 1">XII RPL 1</option>
                <option value="XII RPL 2">XII RPL 2</option>
            </select><br><br>
            <label for="anak">Nama Anak:</label><br>
            <select id="anak" name="anak" required>
                <option value="">Pilih Nama Anak</option>
            </select><br><br>
        `;
    }

    if (peran) {
        popup.classList.add('active');
    } else {
        popup.classList.remove('active');
    }
});


function loadNamaAnak() {
    var kelas = document.getElementById('kelas').value;
    var anakSelect = document.getElementById('anak');
    anakSelect.innerHTML = '<option value="">Loading...</option>';

    if (kelas) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', 'get_anak.php?kelas=' + kelas, true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var anakList = JSON.parse(xhr.responseText);
                anakSelect.innerHTML = '<option value="">Pilih Nama Anak</option>';
                anakList.forEach(function(anak) {
                    var option = document.createElement('option');
                    option.value = anak.NAMALENGKAP;
                    option.textContent = anak.NAMALENGKAP;
                    anakSelect.appendChild(option);
                });
            }
        };
        xhr.send();
    }
}

function closePopup() {
    var popup = document.getElementById('popup');
    popup.classList.remove('active');
}
