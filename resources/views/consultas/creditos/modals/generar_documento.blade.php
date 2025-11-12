<!-- Modal: Generar Documento de Pago desde Punto de Venta -->

<style>
  .modal-header-custom {
    color: white;
  }

  .modal-content {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
    overflow: hidden;
  }

  .modal-body {
    background-color: #f9fafb;
  }

  .form-label {
    color: #374151;
  }

  .form-control, .form-select {
    border-radius: 0.75rem;
  }

  .list-group-item {
    border: none;
    background: #ffffff;
    padding: 0.75rem 1rem;
    margin-bottom: 0.5rem;
    border-radius: 0.5rem;
    box-shadow: 0 1px 4px rgba(0,0,0,0.05);
  }

  .modal-footer {
    background-color: #f1f5f9;
  }

  .btn-primary {
    background-color: #2563eb;
    border: none;
  }

  .btn-primary:hover {
    background-color: #1d4ed8;
  }

  .btn-outline-secondary:hover {
    background-color: #e5e7eb;
  }

  @media (max-width: 576px) {
    .modal-dialog {
      margin: 1rem;
    }
  }
</style>

<div class="modal fade" id="modalGenerarDocumento" tabindex="-1" aria-labelledby="modalGenerarDocumentoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header modal-header-custom px-4 py-3">
        <h5 class="modal-title fw-bold d-flex align-items-center" id="modalGenerarDocumentoLabel">
          <i class="fas fa-file-invoice-dollar me-2"></i> Generar Documento de Pago
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body px-4 py-4">
        <div class="row g-3 align-items-end mb-4">
          <div class="col-md-4">
            <label for="tipoComprobante" class="form-label fw-semibold">Tipo de Comprobante</label>
            <select class="form-select shadow-sm" id="tipoComprobante">
              <option value="boleta">Boleta (DNI)</option>
              <option value="factura">Factura (RUC)</option>
            </select>
          </div>

          <div class="col-md-8">
            <label for="documentInput" class="form-label fw-semibold" id="documentLabel">DNI</label>
            <div class="input-group shadow-sm">
              <input type="text" class="form-control" id="documentInput" placeholder="Ingrese DNI o RUC" maxlength="11">
              <button class="btn btn-outline-secondary" type="button" id="btnBuscarCliente" title="Buscar cliente">
                <i class="fas fa-search"></i>
              </button>
            </div>
          </div>

          <div class="col-12">
            <label for="nombreCliente" class="form-label fw-semibold">Nombre / Razón Social</label>
            <input type="text" class="form-control shadow-sm" id="nombreCliente" disabled placeholder="Aquí se mostrará el nombre o razón social">
          </div>
        </div>

        <h6 class="fw-bold text-secondary mb-2"><i class="fas fa-list-ul me-1"></i> Créditos Seleccionados</h6>
        <ul id="detalleCreditosSeleccionados" class="list-group mb-4"></ul>

        <div class="row text-end small text-muted mb-3">
          <div class="col-6 offset-6">
            <div>Op. Gravada: <span id="subtotalOperacion" class="text-dark fw-bold">S/ 0.00</span></div>
            <div>IGV (18%): <span id="igvOperacion" class="text-dark fw-bold">S/ 0.00</span></div>
          </div>
        </div>

        <div class="text-end fw-bold fs-5">
          Total a pagar: <span class="text-success">S/ <span id="totalPagar">0.00</span></span>
        </div>
      </div>

      <div class="modal-footer d-flex justify-content-between px-4 py-3">
        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">
          <i class="fas fa-times me-1"></i> Cancelar
        </button>
        <button type="button" class="btn btn-primary rounded-pill px-4" id="btnConfirmarPago">
          <i class="fas fa-check-circle me-1"></i> Generar Documento
        </button>
      </div>
    </div>
  </div>
</div>


  

  <script>
    document.getElementById('btnConfirmarPago').addEventListener('click', () => {
      const tipo = tipoComprobante.value;
      const docValue = documentInput.value.trim();
      const selectedCreditIds = Array.from(document.querySelectorAll('.row-checkbox:checked'))
          .map(cb => cb.value);

      fetch("{{ route('tenant.consultas.creditos.generar_documento') }}", {
          method: "POST",
          headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': "{{ csrf_token() }}"
          },
          body: JSON.stringify({
              tipo_comprobante: tipo,
              documento: docValue,
              creditos: selectedCreditIds,
              nombre_razon_social: document.getElementById('nombreCliente').value
          })
      })
      .then(res => res.json())
      .then(data => {
          if (data.success) {
              const modal = bootstrap.Modal.getInstance(document.getElementById('modalGenerarDocumento'));
              modal.hide();

              Swal.fire('Éxito', data.message, 'success');
              window.open(data.url_pdf, '_blank');
              window.creditosTable.ajax.reload();
          } else {
              Swal.fire('Error', data.message, 'error');
          }
      });
  });

</script>

<script>
  document.getElementById('btnBuscarCliente').addEventListener('click', async () => {
      const tipo = document.getElementById('tipoComprobante').value;
      const input = document.getElementById('documentInput');
      const nombreCliente = document.getElementById('nombreCliente');
      const numeroDocumento = input.value.trim();
  
      if (!numeroDocumento) {
          Swal.fire({
              icon: 'warning',
              title: 'Campo vacío',
              text: 'Por favor, ingrese un número de documento.'
          });
          return;
      }
  
      if ((tipo === 'boleta' && numeroDocumento.length !== 8) ||
          (tipo === 'factura' && numeroDocumento.length !== 11)) {
          Swal.fire({
              icon: 'warning',
              title: tipo === 'boleta' ? 'DNI inválido' : 'RUC inválido',
              text: tipo === 'boleta'
                  ? 'Debe ingresar un DNI válido de 8 dígitos.'
                  : 'Debe ingresar un RUC válido de 11 dígitos.'
          });
          return;
      }
  
      // Mostrar spinner si tienes una función
      if (typeof mostrarAnimacion1 === 'function') mostrarAnimacion1();
  
      try {
          let urlLocal = tipo === 'boleta'
              ? `/api/customers/${numeroDocumento}`
              : `/api/customers/ruc/${numeroDocumento}`;
  
          const localResponse = await fetch(urlLocal);
          const localData = await localResponse.json();
  
          if (localData.data || localData.razon_social) {
              nombreCliente.value = localData.data?.name || localData.razon_social;
              if (typeof ocultarAnimacion1 === 'function') ocultarAnimacion1();
              return;
          }
  
          // Si no lo encuentra localmente, consultar RENIEC o SUNAT
          const urlExterno = tipo === 'boleta'
              ? `/landlord/dni/${numeroDocumento}`
              : `/landlord/ruc/${numeroDocumento}`;
  
          const externaResponse = await fetch(urlExterno, {
              method: 'GET',
              headers: {
                  'Content-Type': 'application/json',
                  'Accept': 'application/json'
              }
          });
  
          if (!externaResponse.ok) throw new Error('Error HTTP');
  
          const externaData = await externaResponse.json();
  
          if (!externaData.success) {
              Swal.fire({
                  icon: 'error',
                  title: 'No encontrado',
                  text: tipo === 'boleta'
                      ? 'DNI inválido o no existe en RENIEC.'
                      : 'RUC inválido o no existe en SUNAT.'
              });
          } else {
              nombreCliente.value = tipo === 'boleta'
                  ? externaData.data.nombre_completo
                  : externaData.data.nombre_o_razon_social;
          }
      } catch (error) {
          console.error('Error en la consulta:', error);
          Swal.fire({
              icon: 'error',
              title: 'Error',
              text: 'Hubo un problema al consultar. Intenta nuevamente más tarde.'
          });
      } finally {
          if (typeof ocultarAnimacion1 === 'function') ocultarAnimacion1();
      }
  });
  </script>
  