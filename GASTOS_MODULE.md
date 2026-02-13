# Gastos (Expenses) CRUD Module

## Overview
Complete CRUD module for managing expenses in the inventory system. Includes support for file uploads, filtering, pagination, and comprehensive validations.

## Files Created
- **controllers/GastosController.php** (431 lines) - Main controller with all CRUD operations
- **views/gastos/index.php** (211 lines) - List view with filters and totals
- **views/gastos/crear.php** (165 lines) - Create form
- **views/gastos/editar.php** (198 lines) - Edit form
- **public/js/gastos.js** (40 lines) - Shared JavaScript utilities

## Features

### Controller Methods
1. **index()** - Lists all expenses with pagination and filtering
2. **crear()** - Displays the create form
3. **guardar()** - Saves new expense with validations
4. **editar($id)** - Shows edit form for existing expense
5. **actualizar()** - Updates existing expense

### Form Fields
- **categoria_id** (required) - Expense category from categorias_gasto
- **concepto** (required) - Brief description of expense
- **descripcion** (optional) - Detailed description
- **monto** (required) - Amount (must be > 0)
- **fecha_gasto** (required) - Date of expense (cannot be future)
- **forma_pago** (required) - Payment method (efectivo, tarjeta, transferencia, cheque)
- **servicio_id** (optional) - Related service
- **cliente_id** (optional) - Related client
- **proveedor_id** (optional) - Related supplier
- **comprobante** (optional) - Receipt/invoice file (PDF, JPG, PNG)
- **observaciones** (optional) - Additional notes

### Filters (Index View)
- Date range (fecha_desde, fecha_hasta)
- Category filter
- Payment method filter
- Displays total expenses amount

### Validations
- All required fields checked
- Amount must be greater than 0
- Date cannot be in the future
- File upload: Only PDF, JPG, PNG allowed
- Maximum file size validation
- Foreign key validation

### Security Features
- Permission-based access control
- SQL injection prevention (prepared statements)
- XSS protection (htmlspecialchars)
- Secure filename generation (random_bytes)
- File type validation
- Audit logging for all operations

### File Upload
- Files saved to: `/public/uploads/comprobantes/`
- Secure filename format: `comprobante_{timestamp}_{random32hex}.{ext}`
- Old files automatically deleted on update
- Directory created automatically if not exists

### Business Rules
- **usuario_registro_id** automatically set to current user
- Audit trail for all create/update operations
- Pagination with 15 records per page
- Real-time total calculation based on filters

## Routes
All routes registered in `index.php`:
- `GET /gastos` - List expenses
- `GET /gastos/crear` - Show create form
- `POST /gastos/guardar` - Save new expense
- `GET /gastos/editar/{id}` - Show edit form
- `POST /gastos/actualizar` - Update expense

## Permissions Required
- `gastos.leer` - View expenses
- `gastos.crear` - Create new expenses
- `gastos.actualizar` - Edit/update expenses

## Database Table
```sql
CREATE TABLE gastos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categoria_id INT NOT NULL,
    concepto VARCHAR(200) NOT NULL,
    descripcion TEXT,
    monto DECIMAL(10,2) NOT NULL,
    fecha_gasto DATE NOT NULL,
    forma_pago ENUM('efectivo', 'tarjeta', 'transferencia', 'cheque') NOT NULL,
    servicio_id INT,
    cliente_id INT,
    proveedor_id INT,
    comprobante VARCHAR(255),
    observaciones TEXT,
    usuario_registro_id INT NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_gasto(id),
    FOREIGN KEY (servicio_id) REFERENCES servicios(id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (proveedor_id) REFERENCES proveedores(id),
    FOREIGN KEY (usuario_registro_id) REFERENCES usuarios(id)
);
```

## JavaScript Utilities
The `gastos.js` module provides:
- `getCurrentDateString()` - Get current date in ISO format
- `initializeDateValidation(inputId)` - Setup date picker validation
- `initializeServicioClienteMapping()` - Auto-select client when service chosen
- `initializeGastosForm(servicioClienteMap)` - Initialize complete form

## Usage Example

### Creating a New Expense
1. Navigate to `/gastos`
2. Click "Registrar Gasto" button
3. Fill in the form:
   - Select category
   - Enter concept and amount
   - Choose date and payment method
   - Optionally select related service/client/supplier
   - Upload receipt if available
4. Submit form
5. Redirect to index with success message

### Filtering Expenses
1. Use filter form at top of index
2. Select date range, category, or payment method
3. Click "Buscar" to apply filters
4. Total amount updates based on filters

## Testing
Run PHP syntax check:
```bash
php -l controllers/GastosController.php
php -l views/gastos/crear.php
php -l views/gastos/editar.php
php -l views/gastos/index.php
```

## Security Scan Results
- CodeQL: 0 alerts
- No SQL injection vulnerabilities
- XSS protection implemented
- Secure file upload handling
- Proper error handling

## Future Enhancements
- Export to Excel/PDF
- Bulk delete functionality
- Advanced reporting and analytics
- Email notifications for large expenses
- Budget tracking and alerts
- Receipt OCR for automatic data entry
