// --- MODAL PARA PEDIR UNA CONTRASEÑA NUEVA ---
function pedirNuevaContrasena() {
    return new Promise((resolve) => {
        const modal = document.getElementById('modal-restablecer');
        const input = document.getElementById('modal-input-contrasena');
        const btnGuardar = document.getElementById('modal-btn-restablecer-confirmar');
        const btnCancelar = document.getElementById('modal-btn-restablecer-cancelar');

        if (!modal) {
            resolve(null);
            return;
        }

        input.value = '';
        modal.style.display = 'flex';
        input.focus();

        function limpiar(respuesta) {
            modal.style.display = 'none';
            btnGuardar.removeEventListener('click', responderGuardar);
            btnCancelar.removeEventListener('click', responderCancelar);
            resolve(respuesta);
        }

        function responderGuardar() {
            const valor = input.value.trim();
            if (valor.length < 8) {
                alert('La contraseña debe tener al menos 8 caracteres.');
                return; // no cerramos el modal, deja corregir
            }
            limpiar(valor);
        }

        function responderCancelar() { limpiar(null); }

        btnGuardar.addEventListener('click', responderGuardar);
        btnCancelar.addEventListener('click', responderCancelar);
    });
}

// --- MODAL DE CONFIRMACIÓN PROPIO (reemplaza a confirm() nativo) ---
function confirmarAccion(mensaje) {
    return new Promise((resolve) => {
        const modal = document.getElementById('modal-confirmacion');
        const textoMensaje = document.getElementById('modal-mensaje');
        const btnSi = document.getElementById('modal-btn-si');
        const btnNo = document.getElementById('modal-btn-no');

        if (!modal) {
            resolve(false);
            return;
        }

        textoMensaje.textContent = mensaje;
        modal.style.display = 'flex';

        function limpiar(respuesta) {
            modal.style.display = 'none';
            btnSi.removeEventListener('click', responderSi);
            btnNo.removeEventListener('click', responderNo);
            resolve(respuesta);
        }

        function responderSi() { limpiar(true); }
        function responderNo() { limpiar(false); }

        btnSi.addEventListener('click', responderSi);
        btnNo.addEventListener('click', responderNo);
    });
}

document.addEventListener('DOMContentLoaded', () => {

    const tabla = document.getElementById('tabla-contenido');
    const btnAgregarFila = document.querySelector('.btn-agregar-fila');

    if (tabla && btnAgregarFila) {

        tabla.addEventListener('click', async (evento) => {

            // --- ELIMINAR ARTÍCULO ---
            if (evento.target.classList.contains('btn-eliminar')) {
                const fila = evento.target.closest('tr');
                const idArticulo = fila.dataset.idArticulo;

                const confirmado = await confirmarAccion('¿Eliminar este artículo y todas sus unidades?');
                 if (!confirmado) return;

                const datos = new URLSearchParams();
                datos.append('id_articulo', idArticulo);

                fetch('eliminar_articulo.php', { method: 'POST', body: datos })
                    .then(r => r.json())
                    .then(resultado => {
                        if (resultado.exito) fila.remove();
                        else alert('Error: ' + resultado.mensaje);
                    })
                    .catch(() => alert('Error al conectar con el servidor.'));
            }

            // --- CANCELAR fila nueva ---
            if (evento.target.classList.contains('btn-cancelar-fila')) {
                document.getElementById('fila-nueva').remove();
            }

            // --- GUARDAR fila nueva ---
            if (evento.target.classList.contains('btn-guardar-fila')) {
                const codigo = document.getElementById('input-codigo').value.trim();
                const nombre = document.getElementById('input-nombre').value.trim();

                if (!codigo || !nombre) {
                    alert('Código y nombre son obligatorios.');
                    return;
                }

                const datos = new URLSearchParams();
                datos.append('id_salon', idSalonActual);
                datos.append('codigo', codigo);
                datos.append('nombre', nombre);

                fetch('agregar_articulo.php', { method: 'POST', body: datos })
                    .then(r => r.json())
                    .then(resultado => {
                        if (resultado.exito) location.reload();
                        else alert('Error: ' + resultado.mensaje);
                    })
                    .catch(() => alert('Error al conectar con el servidor.'));
            }

                        // --- ENTRAR EN MODO EDICIÓN de un artículo existente ---
            if (evento.target.classList.contains('btn-editar-articulo')) {
                const fila = evento.target.closest('tr');
                const idArticulo = fila.dataset.idArticulo;
                const celdas = fila.querySelectorAll('td');

                const codigoActual = celdas[0].textContent.trim();
                const nombreActual = celdas[1].textContent.trim();

                celdas[0].innerHTML = `<input type="text" class="input-codigo-edicion" value="${codigoActual}">`;
                celdas[1].innerHTML = `<input type="text" class="input-nombre-edicion" value="${nombreActual}">`;
                celdas[4].innerHTML = `
                    <button class="btn-guardar-articulo" data-id-articulo="${idArticulo}">Guardar</button>
                    <button class="btn-cancelar-edicion-articulo">Cancelar</button>
                `;
            }

            // --- CANCELAR edición de artículo ---
            if (evento.target.classList.contains('btn-cancelar-edicion-articulo')) {
                location.reload();
            }

            // --- GUARDAR edición de artículo ---
            if (evento.target.classList.contains('btn-guardar-articulo')) {
                const fila = evento.target.closest('tr');
                const idArticulo = evento.target.dataset.idArticulo;
                const codigo = fila.querySelector('.input-codigo-edicion').value.trim();
                const nombre = fila.querySelector('.input-nombre-edicion').value.trim();

                if (!codigo || !nombre) {
                    alert('Código y nombre son obligatorios.');
                    return;
                }

                const datos = new URLSearchParams();
                datos.append('id_articulo', idArticulo);
                datos.append('codigo', codigo);
                datos.append('nombre', nombre);

                fetch('editar_articulo.php', { method: 'POST', body: datos })
                    .then(r => r.json())
                    .then(resultado => {
                        if (resultado.exito) location.reload();
                        else alert('Error: ' + resultado.mensaje);
                    })
                    .catch(() => alert('Error al conectar con el servidor.'));
            }
        });

        

        // --- ABRIR fila editable ---
        btnAgregarFila.addEventListener('click', () => {
            if (document.getElementById('fila-nueva')) return;

            const filaNueva = document.createElement('tr');
            filaNueva.id = 'fila-nueva';
            filaNueva.innerHTML = `
                <td><input type="text" id="input-codigo" placeholder="Código"></td>
                <td><input type="text" id="input-nombre" placeholder="Nombre del elemento"></td>
                <td>0</td>
                <td>Sin unidades</td>
                <td>
                    <button class="btn-guardar-fila">Guardar</button>
                    <button class="btn-cancelar-fila">Cancelar</button>
                </td>
            `;

            tabla.appendChild(filaNueva);
            document.getElementById('input-codigo').focus();
        });
    }

    // --- DOBLE CLIC: ir al detalle (funciona en editar.php E inventario.php) ---
    if (tabla) {
        tabla.addEventListener('dblclick', (evento) => {
            if (evento.target.tagName === 'INPUT') return;

            const fila = evento.target.closest('tr');
            if (!fila || fila.id === 'fila-nueva') return;

            const idArticulo = fila.dataset.idArticulo;
            if (!idArticulo) return;

            window.location.href = `detalle.php?id_articulo=${idArticulo}`;
        });
    }

    // --- BÚSQUEDA en la tabla ---
    const inputBusqueda = document.getElementById('busqueda');
    if (inputBusqueda && tabla) {
        inputBusqueda.addEventListener('input', () => {
            const texto = inputBusqueda.value.toLowerCase().trim();
            tabla.querySelectorAll('tr').forEach(fila => {
                fila.style.display = fila.textContent.toLowerCase().includes(texto) ? '' : 'none';
            });
        });
    }

    // --- FILTROS DE ESTADO ---
    const botonesFiltro = document.querySelectorAll('.btn-filtro');
    if (botonesFiltro.length && tabla) {
        botonesFiltro.forEach(boton => {
            boton.addEventListener('click', () => {
                const filtro = boton.dataset.filtro;
                botonesFiltro.forEach(b => b.classList.remove('activo'));
                boton.classList.add('activo');

                tabla.querySelectorAll('tr').forEach(fila => {
                    const celdaEstado = fila.querySelector('.celda-estado');
                    if (!celdaEstado) return;

                    if (filtro === 'todos') {
                        celdaEstado.textContent = fila.dataset.resumen;
                    } else {
                        const cantidad = fila.dataset[filtro];
                        const etiquetas = { bueno: 'Buenos', danado: 'Dañados', mantenimiento: 'Mantenimiento', prestado: 'Prestados' };
                        celdaEstado.textContent = `${cantidad} ${etiquetas[filtro]}`;
                    }
                });
            });
        });
    }

    // --- ELIMINAR USUARIO (solo admin, en usuarios.php) ---
    const tablaUsuarios = document.getElementById('tabla-usuarios');
    if (tablaUsuarios) {
        tablaUsuarios.addEventListener('click', async (evento) => {
            // --- RESTABLECER CONTRASEÑA de un profesor ---
            if (evento.target.classList.contains('btn-restablecer-clave')) {
                const fila = evento.target.closest('tr');
                const idUsuario = fila.dataset.idUsuario;

                const nuevaContrasena = await pedirNuevaContrasena();
                if (!nuevaContrasena) return;

                const datos = new URLSearchParams();
                datos.append('accion', 'restablecer');
                datos.append('id_usuario', idUsuario);
                datos.append('contrasena_nueva', nuevaContrasena);

                fetch('usuarios.php', { method: 'POST', body: datos })
                    .then(() => location.reload())
                    .catch(() => alert('Error al conectar con el servidor.'));
            }
            if (evento.target.classList.contains('btn-eliminar-usuario')) {
                const confirmado = await confirmarAccion('¿Eliminar este profesor?');
                if (!confirmado) return;

                const fila = evento.target.closest('tr');
                const idUsuario = fila.dataset.idUsuario;

                const datos = new URLSearchParams();
                datos.append('id_usuario', idUsuario);

                fetch('eliminar_usuario.php', { method: 'POST', body: datos })
                    .then(r => r.json())
                    .then(resultado => {
                        if (resultado.exito) fila.remove();
                        else alert('Error: ' + resultado.mensaje);
                    })
                    .catch(() => alert('Error al conectar con el servidor.'));
            }
        });
    }

});

// --- BOTONES DE detalle.php ---
document.addEventListener('DOMContentLoaded', () => {

    const btnAgregarFicha = document.getElementById('btn-agregar-ficha');
    const formulario = document.getElementById('formulario-nueva-ficha');
    const btnAgregarCaracteristica = document.getElementById('btn-agregar-caracteristica');
    const contenedorCaracteristicas = document.getElementById('caracteristicas-nuevas');
    const btnGuardarFicha = document.getElementById('btn-guardar-ficha');
    const btnCancelarFicha = document.getElementById('btn-cancelar-ficha');
    const fichasScroll = document.getElementById('fichas-scroll');
    const selectEstado = document.getElementById('select-estado');
    const contenedorDescripcion = document.getElementById('contenedor-descripcion');

if (selectEstado && contenedorDescripcion) {
    selectEstado.addEventListener('change', () => {
        const esPrestado = selectEstado.value === 'Prestado';
        contenedorDescripcion.style.display = esPrestado ? 'block' : 'none';

        if (!esPrestado) {
            document.getElementById('input-descripcion').value = '';
        }
    });
}

    // Abrir el formulario de "Agregar ficha"
    if (btnAgregarFicha) {
        btnAgregarFicha.addEventListener('click', () => {
            formulario.style.display = 'block';
            btnAgregarFicha.style.display = 'none';
        });
    }

    // Agregar un par de campos "nombre / valor" vacíos (para ficha nueva)
    if (btnAgregarCaracteristica) {
        btnAgregarCaracteristica.addEventListener('click', () => {
            const fila = document.createElement('div');
            fila.className = 'caracteristica caracteristica-nueva';
            fila.innerHTML = `
                <input type="text" class="carac-nombre" placeholder="Nombre (ej: Potencia)">
                <input type="text" class="carac-valor" placeholder="Valor (ej: 12 watts)">
                <button type="button" class="btn-quitar-caracteristica">✕</button>
            `;
            contenedorCaracteristicas.appendChild(fila);
        });
    }

    // Quitar un par de campos (ficha nueva)
    if (contenedorCaracteristicas) {
        contenedorCaracteristicas.addEventListener('click', (evento) => {
            if (evento.target.classList.contains('btn-quitar-caracteristica')) {
                evento.target.closest('.caracteristica-nueva').remove();
            }
        });
    }

    // Cancelar formulario de ficha nueva
    if (btnCancelarFicha) {
        btnCancelarFicha.addEventListener('click', () => {
            formulario.style.display = 'none';
            btnAgregarFicha.style.display = 'block';
            document.getElementById('select-estado').value = '';
            document.getElementById('input-descripcion').value = '';
            contenedorCaracteristicas.innerHTML = '';
        });
    }    

        // Guardar ficha nueva
    if (btnGuardarFicha) {
        btnGuardarFicha.addEventListener('click', () => {
            const estado = document.getElementById('select-estado').value;
            const estadosValidos = ['Bueno', 'Dañado', 'Mantenimiento', 'Prestado'];

            if (!estado || !estadosValidos.includes(estado)) {
                alert('Debes seleccionar un estado válido.');
                return;
            }

            const descripcion = document.getElementById('input-descripcion').value.trim();

            if (estado === 'Prestado' && descripcion === '') {
                alert('Debes indicar una descripción cuando el estado es "Prestado".');
                return;
            }

            const datos = new URLSearchParams();
            datos.append('id_articulo', idArticuloActual);
            datos.append('estado', estado);
            datos.append('descripcion', descripcion);

            document.querySelectorAll('.caracteristica-nueva').forEach(fila => {
                const nombre = fila.querySelector('.carac-nombre').value.trim();
                const valor = fila.querySelector('.carac-valor').value.trim();
                if (nombre && valor) {
                    datos.append('carac_nombre[]', nombre);
                    datos.append('carac_valor[]', valor);
                }
            });

            fetch('agregar_unidad.php', { method: 'POST', body: datos })
                .then(r => r.json())
                .then(resultado => {
                    if (resultado.exito) location.reload();
                    else alert('Error: ' + resultado.mensaje);
                })
                .catch(() => alert('Error al conectar con el servidor.'));
        });
    }
    
    // --- Acciones dentro de cada ficha existente (Eliminar, Editar, y todo lo del modo edición) ---
    if (fichasScroll) {
        fichasScroll.addEventListener('click', async (evento) => {

            // --- ELIMINAR UNIDAD ---
            if (evento.target.classList.contains('btn-eliminar-unidad')) {
                const confirmado = await confirmarAccion('¿Eliminar esta ficha?');
                if (!confirmado) return;

                const ficha = evento.target.closest('.ficha-articulo');
                const idUnidad = ficha.dataset.idUnidad;

                const datos = new URLSearchParams();
                datos.append('id_unidad', idUnidad);

                fetch('eliminar_unidad.php', { method: 'POST', body: datos })
                    .then(r => r.json())
                    .then(resultado => {
                        if (resultado.exito) ficha.remove();
                        else alert('Error: ' + resultado.mensaje);
                    })
                    .catch(() => alert('Error al conectar con el servidor.'));
            }

            // --- ENTRAR EN MODO EDICIÓN ---
            if (evento.target.classList.contains('btn-editar-unidad')) {
                const ficha = evento.target.closest('.ficha-articulo');
                const vista = ficha.querySelector('.ficha-vista');
                const acciones = ficha.querySelector('.ficha-acciones-individual');
                const estadoActual = vista.dataset.estado;

                const elementoDescripcion = vista.querySelector('.valor-descripcion-actual');
                const descripcionActual = elementoDescripcion ? elementoDescripcion.textContent.trim() : '';

                const caracteristicasActuales = [];
                vista.querySelectorAll('[data-nombre]').forEach(el => {
                    caracteristicasActuales.push({ nombre: el.dataset.nombre, valor: el.dataset.valor });
                });

                let html = `
                    <div class="caracteristica">
                        <span class="nombre-caracteristica">Estado</span>
                        <select class="select-estado-edicion">
                            <option value="Bueno" ${estadoActual === 'Bueno' ? 'selected' : ''}>Bueno</option>
                            <option value="Dañado" ${estadoActual === 'Dañado' ? 'selected' : ''}>Dañado</option>
                            <option value="Mantenimiento" ${estadoActual === 'Mantenimiento' ? 'selected' : ''}>Mantenimiento</option>
                            <option value="Prestado" ${estadoActual === 'Prestado' ? 'selected' : ''}>Prestado</option>
                        </select>
                    </div>
                    <div class="caracteristica contenedor-descripcion-edicion" style="display: ${estadoActual === 'Prestado' ? 'block' : 'none'};">
                        <span class="nombre-caracteristica">Descripción</span>
                        <textarea class="textarea-descripcion-edicion">${descripcionActual}</textarea>
                    </div>
                    <div class="caracteristicas-edicion">
                        ${caracteristicasActuales.map(c => `
                            <div class="caracteristica caracteristica-edicion-fila">
                                <input type="text" class="carac-nombre-edicion" value="${c.nombre}">
                                <input type="text" class="carac-valor-edicion" value="${c.valor}">
                                <button type="button" class="btn-quitar-caracteristica-edicion">✕</button>
                            </div>
                        `).join('')}
                    </div>
                    <button type="button" class="btn-agregar-caracteristica-edicion">+ Agregar característica</button>
                `;

                vista.innerHTML = html;
                // Conectamos el mismo comportamiento de mostrar/ocultar para este formulario de edición
                const selectEdicion = vista.querySelector('.select-estado-edicion');
                const contenedorDescEdicion = vista.querySelector('.contenedor-descripcion-edicion');
                selectEdicion.addEventListener('change', () => {
                    const esPrestado = selectEdicion.value === 'Prestado';
                    contenedorDescEdicion.style.display = esPrestado ? 'block' : 'none';

                    if (!esPrestado) {
                        vista.querySelector('.textarea-descripcion-edicion').value = '';
                    }
                });

                acciones.innerHTML = `
                    <button class="btn-guardar-edicion">Guardar cambios</button>
                    <button class="btn-cancelar-edicion">Cancelar</button>
                `;
            }

            // --- AGREGAR una nueva característica mientras se edita ---
            if (evento.target.classList.contains('btn-agregar-caracteristica-edicion')) {
                const contenedor = evento.target.previousElementSibling;
                const fila = document.createElement('div');
                fila.className = 'caracteristica caracteristica-edicion-fila';
                fila.innerHTML = `
                    <input type="text" class="carac-nombre-edicion" placeholder="Nombre (ej: Potencia)">
                    <input type="text" class="carac-valor-edicion" placeholder="Valor (ej: 12 watts)">
                    <button type="button" class="btn-quitar-caracteristica-edicion">✕</button>
                `;
                contenedor.appendChild(fila);
            }

            // --- QUITAR una característica mientras se edita ---
            if (evento.target.classList.contains('btn-quitar-caracteristica-edicion')) {
                evento.target.closest('.caracteristica-edicion-fila').remove();
            }

            // --- CANCELAR edición ---
            if (evento.target.classList.contains('btn-cancelar-edicion')) {
                location.reload();
            }

            // --- GUARDAR CAMBIOS de edición ---
            if (evento.target.classList.contains('btn-guardar-edicion')) {
                const ficha = evento.target.closest('.ficha-articulo');
                const idUnidad = ficha.dataset.idUnidad;
                const estado = ficha.querySelector('.select-estado-edicion').value;
                const descripcion = ficha.querySelector('.textarea-descripcion-edicion').value.trim();

                if (estado === 'Prestado' && descripcion === '') {
                    alert('Debes indicar una descripción cuando el estado es "Prestado".');
                    return;
                }

                const datos = new URLSearchParams();
                datos.append('id_unidad', idUnidad);
                datos.append('estado', estado);
                datos.append('descripcion', descripcion);

                ficha.querySelectorAll('.caracteristica-edicion-fila').forEach(fila => {
                    const nombre = fila.querySelector('.carac-nombre-edicion').value.trim();
                    const valor = fila.querySelector('.carac-valor-edicion').value.trim();
                    if (nombre && valor) {
                        datos.append('carac_nombre[]', nombre);
                        datos.append('carac_valor[]', valor);
                    }
                });

                fetch('editar_unidad.php', { method: 'POST', body: datos })
                    .then(r => r.json())
                    .then(resultado => {
                        if (resultado.exito) location.reload();
                        else alert('Error: ' + resultado.mensaje);
                    })
                    .catch(() => alert('Error al conectar con el servidor.'));
            }
        });
    }

    // Búsqueda en fichas
    const inputBuscarFicha = document.getElementById('buscar-ficha');
    if (inputBuscarFicha && fichasScroll) {
        inputBuscarFicha.addEventListener('input', () => {
            const texto = inputBuscarFicha.value.toLowerCase().trim();
            fichasScroll.querySelectorAll('.ficha-articulo').forEach(ficha => {
                if (ficha.id === 'formulario-nueva-ficha') return;
                ficha.style.display = ficha.textContent.toLowerCase().includes(texto) ? '' : 'none';
            });
        });
    }

});

// --- CONFIRMACIÓN al salir desde salones.php ---
document.addEventListener('DOMContentLoaded', () => {

    const btnVolverInicio = document.getElementById('btn-volver-inicio');

    if (btnVolverInicio) {
        btnVolverInicio.addEventListener('click', async (evento) => {
            evento.preventDefault(); // pausamos SIEMPRE la navegación primero

            const confirmado = await confirmarAccion('¿Quieres salir? Tendrás que volver a validar tus datos después.');

            if (confirmado) {
                window.location.href = btnVolverInicio.href; // si dijo que sí, navegamos manualmente
            }
        });
    }

});

