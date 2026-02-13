/**
 * Gastos module shared JavaScript utilities
 */

// Get current date string in ISO format
function getCurrentDateString() {
    return new Date().toISOString().split('T')[0];
}

// Set max date to today for date inputs
function initializeDateValidation(inputId) {
    const fechaInput = document.getElementById(inputId);
    if (fechaInput) {
        fechaInput.max = getCurrentDateString();
    }
}

// Auto-select cliente when servicio is selected
function initializeServicioClienteMapping(servicioSelectId, clienteSelectId, servicioClienteMap) {
    const servicioSelect = document.getElementById(servicioSelectId);
    const clienteSelect = document.getElementById(clienteSelectId);
    
    if (servicioSelect && clienteSelect) {
        servicioSelect.addEventListener('change', function() {
            const servicioId = this.value;
            if (servicioId && servicioClienteMap[servicioId]) {
                clienteSelect.value = servicioClienteMap[servicioId];
            }
        });
    }
}

// Initialize gastos form
function initializeGastosForm(servicioClienteMap) {
    // Date validation
    initializeDateValidation('fecha_gasto');
    
    // Servicio-Cliente mapping
    initializeServicioClienteMapping('servicio_id', 'cliente_id', servicioClienteMap);
}
