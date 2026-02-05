/**
 * UTILIDADES JAVASCRIPT - SISTEMA REFUGIO
 * Funciones comunes para todo el sistema
 */

// ============================================
// CONFIGURACIÓN GLOBAL
// ============================================

const Config = {
  alertAutoClose: 5000, // milisegundos
  dateFormat: 'dd/mm/yyyy',
  minPasswordLength: 6,
};

// ============================================
// GESTIÓN DE ALERTAS
// ============================================

/**
 * Mostrar alerta temporal
 */
function showAlert(message, type = 'info') {
  const alertDiv = document.createElement('div');
  alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
  alertDiv.setAttribute('role', 'alert');
  alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

  document.querySelector('.container-fluid').prepend(alertDiv);

  // Auto cerrar después de 5 segundos
  setTimeout(() => {
    alertDiv.classList.remove('show');
    setTimeout(() => alertDiv.remove(), 300);
  }, Config.alertAutoClose);
}

/**
 * Confirmar acción
 */
function confirmAction(message, callback) {
  if (confirm(message)) {
    callback();
  }
}

// ============================================
// VALIDACIÓN DE FORMULARIOS
// ============================================

/**
 * Validar email
 */
function validateEmail(email) {
  const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  return re.test(email);
}

/**
 * Validar DNI español
 */
function validateDNI(dni) {
  const dniRegex = /^[0-9]{8}[A-Z]$/;
  if (!dniRegex.test(dni)) return false;

  const letters = 'TRWAGMYFPDXBNJZSQVHLCKE';
  const number = dni.substr(0, 8);
  const letter = dni.substr(8, 1);

  return letters[number % 23] === letter;
}

/**
 * Validar fecha (no en el pasado)
 */
function validateFutureDate(dateString) {
  const date = new Date(dateString);
  const today = new Date();
  today.setHours(0, 0, 0, 0);
  return date >= today;
}

/**
 * Validar rango de fechas
 */
function validateDateRange(startDate, endDate) {
  const start = new Date(startDate);
  const end = new Date(endDate);
  return end > start;
}

// ============================================
// FORMATO DE DATOS
// ============================================

/**
 * Formatear fecha a dd/mm/yyyy
 */
function formatDate(dateString) {
  const date = new Date(dateString);
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  return `${day}/${month}/${year}`;
}

/**
 * Calcular días entre dos fechas
 */
function daysBetween(startDate, endDate) {
  const start = new Date(startDate);
  const end = new Date(endDate);
  const diffTime = Math.abs(end - start);
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

/**
 * Formatear número de teléfono
 */
function formatPhone(phone) {
  return phone.replace(/(\d{3})(\d{3})(\d{3})/, '$1 $2 $3');
}

// ============================================
// UTILIDADES DE UI
// ============================================

/**
 * Mostrar/ocultar loader
 */
function toggleLoader(show = true) {
  let loader = document.getElementById('globalLoader');

  if (!loader) {
    loader = document.createElement('div');
    loader.id = 'globalLoader';
    loader.className = 'loading-overlay';
    loader.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
            </div>
        `;
    document.body.appendChild(loader);
  }

  loader.style.display = show ? 'flex' : 'none';
}

/**
 * Deshabilitar botón y mostrar loading
 */
function setButtonLoading(button, loading = true) {
  const btn = typeof button === 'string' ? document.querySelector(button) : button;

  if (loading) {
    btn.disabled = true;
    btn.dataset.originalHtml = btn.innerHTML;
    btn.innerHTML = `
            <span class="spinner-border spinner-border-sm me-2"></span>
            Procesando...
        `;
  } else {
    btn.disabled = false;
    btn.innerHTML = btn.dataset.originalHtml || btn.innerHTML;
  }
}

/**
 * Copiar al portapapeles
 */
async function copyToClipboard(text) {
  try {
    await navigator.clipboard.writeText(text);
    showAlert('Copiado al portapapeles', 'success');
  } catch (err) {
    showAlert('Error al copiar', 'danger');
  }
}

// ============================================
// AJAX HELPERS
// ============================================

/**
 * Realizar petición fetch con manejo de errores
 */
async function fetchData(url, options = {}) {
  try {
    toggleLoader(true);
    const response = await fetch(url, {
      headers: {
        'Content-Type': 'application/json',
        ...options.headers
      },
      ...options
    });

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Fetch error:', error);
    showAlert('Error al cargar los datos', 'danger');
    return null;
  } finally {
    toggleLoader(false);
  }
}

/**
 * Enviar formulario por AJAX
 */
async function submitForm(form, url = null) {
  const formElement = typeof form === 'string' ? document.querySelector(form) : form;
  const formData = new FormData(formElement);
  const submitUrl = url || formElement.action;

  try {
    const response = await fetch(submitUrl, {
      method: 'POST',
      body: formData
    });

    return await response.json();
  } catch (error) {
    console.error('Form submit error:', error);
    showAlert('Error al enviar el formulario', 'danger');
    return null;
  }
}

// ============================================
// UTILIDADES DE STORAGE
// ============================================

/**
 * Guardar en localStorage
 */
function saveToStorage(key, value) {
  try {
    localStorage.setItem(key, JSON.stringify(value));
    return true;
  } catch (error) {
    console.error('Storage error:', error);
    return false;
  }
}

/**
 * Obtener de localStorage
 */
function getFromStorage(key, defaultValue = null) {
  try {
    const item = localStorage.getItem(key);
    return item ? JSON.parse(item) : defaultValue;
  } catch (error) {
    console.error('Storage error:', error);
    return defaultValue;
  }
}

/**
 * Eliminar de localStorage
 */
function removeFromStorage(key) {
  try {
    localStorage.removeItem(key);
    return true;
  } catch (error) {
    console.error('Storage error:', error);
    return false;
  }
}

// ============================================
// UTILIDADES DE TABLA
// ============================================

/**
 * Filtrar tabla
 */
function filterTable(inputId, tableId) {
  const input = document.getElementById(inputId);
  const filter = input.value.toUpperCase();
  const table = document.getElementById(tableId);
  const rows = table.getElementsByTagName('tr');

  for (let i = 1; i < rows.length; i++) {
    let shouldShow = false;
    const cells = rows[i].getElementsByTagName('td');

    for (let j = 0; j < cells.length; j++) {
      const cell = cells[j];
      if (cell) {
        const textValue = cell.textContent || cell.innerText;
        if (textValue.toUpperCase().indexOf(filter) > -1) {
          shouldShow = true;
          break;
        }
      }
    }

    rows[i].style.display = shouldShow ? '' : 'none';
  }
}

/**
 * Ordenar tabla
 */
function sortTable(tableId, columnIndex, ascending = true) {
  const table = document.getElementById(tableId);
  const tbody = table.getElementsByTagName('tbody')[0];
  const rows = Array.from(tbody.getElementsByTagName('tr'));

  rows.sort((a, b) => {
    const aVal = a.getElementsByTagName('td')[columnIndex].textContent;
    const bVal = b.getElementsByTagName('td')[columnIndex].textContent;

    if (ascending) {
      return aVal.localeCompare(bVal);
    } else {
      return bVal.localeCompare(aVal);
    }
  });

  rows.forEach(row => tbody.appendChild(row));
}

// ============================================
// INICIALIZACIÓN
// ============================================

document.addEventListener('DOMContentLoaded', function () {
  // Auto-cerrar alertas después de 5 segundos
  const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
  alerts.forEach(alert => {
    setTimeout(() => {
      const closeBtn = alert.querySelector('.btn-close');
      if (closeBtn) closeBtn.click();
    }, Config.alertAutoClose);
  });

  // Confirmar eliminaciones
  document.querySelectorAll('[data-confirm]').forEach(element => {
    element.addEventListener('click', function (e) {
      if (!confirm(this.dataset.confirm)) {
        e.preventDefault();
      }
    });
  });

  // Validación de formularios Bootstrap
  const forms = document.querySelectorAll('.needs-validation');
  forms.forEach(form => {
    form.addEventListener('submit', function (event) {
      if (!form.checkValidity()) {
        event.preventDefault();
        event.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  });
});

// ============================================
// EXPORTAR PARA USO GLOBAL
// ============================================

window.RefugioUtils = {
  showAlert,
  confirmAction,
  validateEmail,
  validateDNI,
  validateFutureDate,
  validateDateRange,
  formatDate,
  daysBetween,
  formatPhone,
  toggleLoader,
  setButtonLoading,
  copyToClipboard,
  fetchData,
  submitForm,
  saveToStorage,
  getFromStorage,
  removeFromStorage,
  filterTable,
  sortTable
};

console.log('✅ Refugio Utils cargadas correctamente');
