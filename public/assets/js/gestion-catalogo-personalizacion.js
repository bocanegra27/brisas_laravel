// js/gestion-catalogo-personalizacion.js
// Lógica para gestión de catálogo de personalización en admin

document.addEventListener('DOMContentLoaded', function() {
    cargarOpciones();
    cargarValores();

    // Mostrar modal para agregar valor
    document.getElementById('btn-agregar-valor').addEventListener('click', function() {
        limpiarModal();
        document.getElementById('modalValorLabel').textContent = 'Agregar Valor';
        new bootstrap.Modal(document.getElementById('modalValor')).show();
    });

    // Vista previa de imagen
    document.getElementById('imagen').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('img-preview');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                preview.src = ev.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '';
            preview.style.display = 'none';
        }
    });

    // Guardar valor (agregar o editar)
    document.getElementById('form-valor').addEventListener('submit', function(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        const valorId = formData.get('valor_id');
        const url = valorId ? '../php/personalizacion/editar_valor.php' : '../php/personalizacion/agregar_valor.php';
        fetch(url, {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                bootstrap.Modal.getInstance(document.getElementById('modalValor')).hide();
                cargarValores();
            } else {
                alert(data.error || 'Error al guardar');
            }
        });
    });
});

function cargarOpciones() {
    fetch('../php/personalizacion/listar_catalogo.php')
        .then(r => r.json())
        .then(data => {
            const select = document.getElementById('opcion');
            select.innerHTML = '';
            data.opciones.forEach(op => {
                const opt = document.createElement('option');
                opt.value = op.opc_id;
                opt.textContent = op.opc_nombre;
                select.appendChild(opt);
            });
        });
}

function cargarValores() {
    fetch('../php/personalizacion/listar_catalogo.php')
        .then(r => r.json())
        .then(data => {
            const tbody = document.querySelector('#tabla-catalogo tbody');
            tbody.innerHTML = '';
            data.valores.forEach(val => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td>${val.opc_nombre}</td>
                    <td>${val.val_nombre}</td>
                    <td><img src="../${val.val_imagen}" class="img-preview"></td>
                    <td>
                        <button class="btn btn-sm btn-primary me-1" onclick="editarValor(${val.val_id})"><i class="bi bi-pencil"></i></button>
                        <button class="btn btn-sm btn-danger" onclick="eliminarValor(${val.val_id})"><i class="bi bi-trash"></i></button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        });
}

function limpiarModal() {
    document.getElementById('valor_id').value = '';
    document.getElementById('opcion').selectedIndex = 0;
    document.getElementById('nombre').value = '';
    document.getElementById('imagen').value = '';
    document.getElementById('img-preview').src = '';
    document.getElementById('img-preview').style.display = 'none';
}

window.editarValor = function(id) {
    fetch('../php/personalizacion/listar_catalogo.php')
        .then(r => r.json())
        .then(data => {
            const valor = data.valores.find(v => v.val_id == id);
            if (valor) {
                document.getElementById('valor_id').value = valor.val_id;
                document.getElementById('opcion').value = valor.opc_id;
                document.getElementById('nombre').value = valor.val_nombre;
                document.getElementById('img-preview').src = '../' + valor.val_imagen;
                document.getElementById('img-preview').style.display = 'block';
                document.getElementById('modalValorLabel').textContent = 'Editar Valor';
                new bootstrap.Modal(document.getElementById('modalValor')).show();
            }
        });
}

window.eliminarValor = function(id) {
    if (confirm('¿Seguro que deseas eliminar este valor?')) {
        fetch('../php/personalizacion/eliminar_valor.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'val_id=' + encodeURIComponent(id)
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                cargarValores();
            } else {
                alert(data.error || 'Error al eliminar');
            }
        });
    }
}
