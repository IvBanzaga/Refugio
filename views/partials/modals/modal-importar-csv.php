<!-- Modal Importar CSV de Usuarios -->
<div class="modal fade" id="modalImportarCSV" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="bi bi-file-earmark-arrow-up"></i> Importar Usuarios desde CSV
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" enctype="multipart/form-data" id="formImportarCSV" action="viewAdminMVC.php">
                <input type="hidden" name="accion" value="importar_usuarios_csv">
                <div class="modal-body">
                    <!-- Paso 1: Subir archivo -->
                    <div id="paso1" class="import-step">
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <strong>Instrucciones:</strong>
                            Selecciona un archivo CSV con los datos de los usuarios.
                            El archivo debe contener las siguientes columnas:
                            <strong>Número de Socio, Nombre, Apellido1, Apellido2, DNI, Email, Teléfono</strong>
                            <br><small class="mt-2 d-block">
                            • Los usuarios importados tendrán rol "user" por defecto<br>
                            • La contraseña será el DNI sin letra<br>
                            • Podrás mapear las columnas si tienen nombres diferentes
                            </small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Archivo CSV *</label>
                            <input type="file" class="form-control" name="archivo_csv" id="archivoCsv"
                                   accept=".csv" required onchange="procesarCSV(this)">
                        </div>
                    </div>

                    <!-- Paso 2: Mapear columnas -->
                    <div id="paso2" class="import-step" style="display: none;">
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-triangle"></i>
                            <strong>Mapeo de Columnas:</strong>
                            Asigna cada columna del CSV al campo correspondiente del sistema
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Campo del Sistema</th>
                                        <th>Columna del CSV</th>
                                        <th>Vista Previa</th>
                                    </tr>
                                </thead>
                                <tbody id="mapeoColumnas">
                                    <tr>
                                        <td><strong>Número de Socio *</strong></td>
                                        <td><select name="map_num_socio" class="form-select" id="map_num_socio" required></select></td>
                                        <td><span id="prev_num_socio" class="text-muted">-</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Nombre *</strong></td>
                                        <td><select name="map_nombre" class="form-select" id="map_nombre" required></select></td>
                                        <td><span id="prev_nombre" class="text-muted">-</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Apellido 1 *</strong></td>
                                        <td><select name="map_apellido1" class="form-select" id="map_apellido1" required></select></td>
                                        <td><span id="prev_apellido1" class="text-muted">-</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Apellido 2</strong></td>
                                        <td><select name="map_apellido2" class="form-select" id="map_apellido2"></select></td>
                                        <td><span id="prev_apellido2" class="text-muted">-</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>DNI *</strong></td>
                                        <td><select name="map_dni" class="form-select" id="map_dni" required></select></td>
                                        <td><span id="prev_dni" class="text-muted">-</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Email *</strong></td>
                                        <td><select name="map_email" class="form-select" id="map_email" required></select></td>
                                        <td><span id="prev_email" class="text-muted">-</span></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Teléfono</strong></td>
                                        <td><select name="map_telefono" class="form-select" id="map_telefono"></select></td>
                                        <td><span id="prev_telefono" class="text-muted">-</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-secondary">
                            <strong>Total de registros a importar:</strong> <span id="totalRegistros">0</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-secondary" id="btnVolver" style="display: none;" onclick="volverPaso1()">
                        <i class="bi bi-arrow-left"></i> Volver
                    </button>
                    <button type="submit" class="btn btn-success" id="btnImportar" style="display: none;">
                        <i class="bi bi-check-circle"></i> Importar Usuarios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
let csvData = [];
let csvHeaders = [];

function procesarCSV(input) {
    const file = input.files[0];
    if (!file) return;

    const reader = new FileReader();
    reader.onload = function(e) {
        const text = e.target.result;
        const lines = text.split('\n').filter(line => line.trim());

        if (lines.length < 2) {
            alert('El archivo CSV debe contener al menos una cabecera y una fila de datos');
            input.value = '';
            return;
        }

        // Parsear CSV (simple, sin comillas complejas)
        csvHeaders = lines[0].split(/[,;]/).map(h => h.trim().replace(/^"|"$/g, ''));
        csvData = [];

        for (let i = 1; i < lines.length; i++) {
            const row = lines[i].split(/[,;]/).map(cell => cell.trim().replace(/^"|"$/g, ''));
            if (row.length === csvHeaders.length && row.some(cell => cell)) {
                csvData.push(row);
            }
        }

        if (csvData.length === 0) {
            alert('No se encontraron datos válidos en el archivo CSV');
            input.value = '';
            return;
        }

        // Mostrar paso 2 y llenar los selects
        document.getElementById('paso1').style.display = 'none';
        document.getElementById('paso2').style.display = 'block';
        document.getElementById('btnVolver').style.display = 'inline-block';
        document.getElementById('btnImportar').style.display = 'inline-block';
        document.getElementById('totalRegistros').textContent = csvData.length;

        // Llenar selects de mapeo
        const selects = ['map_num_socio', 'map_nombre', 'map_apellido1', 'map_apellido2', 'map_dni', 'map_email', 'map_telefono'];
        selects.forEach(selectId => {
            const select = document.getElementById(selectId);
            select.innerHTML = '<option value="">-- Sin asignar --</option>';
            csvHeaders.forEach((header, index) => {
                const option = document.createElement('option');
                option.value = index;
                option.textContent = header;
                select.appendChild(option);
            });

            // Auto-mapeo inteligente
            const fieldName = selectId.replace('map_', '').toLowerCase();
            const matchIndex = csvHeaders.findIndex(h => {
                const headerLower = h.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                return headerLower.includes(fieldName) ||
                       (fieldName === 'num_socio' && (headerLower.includes('socio') || headerLower.includes('numero'))) ||
                       (fieldName === 'apellido1' && headerLower.includes('apellido1')) ||
                       (fieldName === 'apellido2' && headerLower.includes('apellido2')) ||
                       (fieldName === 'telefono' && (headerLower.includes('telefono') || headerLower.includes('telf')));
            });

            if (matchIndex !== -1) {
                select.value = matchIndex;
                actualizarVistaPrevia(selectId, matchIndex);
            }

            // Event listener para vista previa
            select.addEventListener('change', function() {
                actualizarVistaPrevia(selectId, this.value);
            });
        });
    };
    reader.readAsText(file);
}

function actualizarVistaPrevia(selectId, columnIndex) {
    const field = selectId.replace('map_', '');
    const prevSpan = document.getElementById('prev_' + field);
    if (columnIndex !== '' && csvData.length > 0) {
        prevSpan.textContent = csvData[0][columnIndex] || '-';
        prevSpan.className = 'text-success fw-bold';
    } else {
        prevSpan.textContent = '-';
        prevSpan.className = 'text-muted';
    }
}

function volverPaso1() {
    document.getElementById('paso1').style.display = 'block';
    document.getElementById('paso2').style.display = 'none';
    document.getElementById('btnVolver').style.display = 'none';
    document.getElementById('btnImportar').style.display = 'none';
}

// Reset al cerrar modal
document.getElementById('modalImportarCSV').addEventListener('hidden.bs.modal', function() {
    document.getElementById('formImportarCSV').reset();
    volverPaso1();
    csvData = [];
    csvHeaders = [];
});
</script>
