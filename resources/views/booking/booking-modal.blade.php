<style>
    .scrollable-dropdown {
        max-height: 200px;
        overflow-y: auto;
    }

    .dropdown-menu .form-check {
        margin-bottom: 5px;
    }
</style>

<div class="modal fade mdlBookingModal" id="bookingModal-{{ $hour_id }}-{{ $today }}-{{ $field->id }}" data-hour="{{ $hour_id }}" data-date="{{ $today }}" data-field="{{ $field->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="bookingForm-{{ $hour_id }}-{{ $today }}-{{ $field->id }}"  enctype="multipart/form-data">
                @csrf
                


                <div class="modal-header">
                    <h5 class="modal-title">
                        Registrar <span id="title-modal-{{ $hour_id }}-{{ $today }}-{{ $field->id }}"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div> 
                <div class="modal-body">
                    <div class="text-center">
                        <span class="h5 text-primary">{{ $field->field }}</span> 
                        <span class="h5 text-primary" id="base-price-{{ $hour_id }}-{{ $today }}-{{ $field->id }}" 
                            data-price="{{ $first_hour >= $hourNight ? $field->night_price : $field->day_price }}">
                            Total S/. {{ $first_hour >= $hourNight ? $field->night_price : $field->day_price }}
                        </span>
                        <br>
                        Horario: {{ $hour }} Fecha: {{ $today }}
                    </div>

                    <input type="hidden" name="juntar_con_ids" id="juntar_con_ids-{{ $hour_id }}-{{ $today }}-{{ $field->id }}">

                    <h5 class="card-title">Cliente</h5>
                    <div class="row mb-3">
                        <!-- DNI -->
                        <div class="col-md-5 col-12">
                            <label for="dni" class="fw-bold">
                                DNI 
                                <span style="cursor: pointer; font-weight: normal; opacity: 0.7; color: #1877f2;" 
                                      onclick="openMdlRecordCustomer({{ $hour_id }}, '{{ $today }}', {{ $field->id }})">
                                    [Ver Historial]
                                </span>
                            </label>
                            <div class="input-group">
                                <input type="text" name="document_number" id="document_number-{{ $hour_id }}-{{ $today }}-{{ $field->id }}" 
                                       class="form-control colorEditable" maxlength="8" pattern="\d{8}" 
                                       title="Ingrese 8 dígitos numéricos" 
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 8)">
                                <button type="button" class="btn btn-primary btn-search" 
                                        data-hour="{{ $hour_id }}" data-date="{{ $today }}" data-field="{{ $field->id }}">
                                    <i class='bx bx-search-alt-2'></i>
                                </button>
                            </div>
                        </div>
                    
                        <!-- RUC -->
                        <div class="col-md-7 col-12 mt-2 mt-md-0">
                            <label for="ruc" class="fw-bold">
                                RUC (Opcional)
                            </label>
                            <div class="input-group">
                                <input type="text" name="ruc_number" id="ruc_number-{{ $hour_id }}-{{ $today }}-{{ $field->id }}" 
                                       class="form-control colorEditable" maxlength="11" pattern="\d{11}" 
                                       title="Ingrese 11 dígitos numéricos" 
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 11)">
                                <button type="button" class="btn btn-primary btn-search-ruc" 
                                        data-hour="{{ $hour_id }}" data-date="{{ $today }}" data-field="{{ $field->id }}">
                                    <i class='bx bx-search-alt-2'></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    
                                      
                

                    <div class="row mb-3">
                        <div class="col-md-8 col-12">
                            <div class="form-group">
                                <label for="name" style="font-weight: bold;">Nombre</label>
                                <input type="text" name="name" id="name-{{ $hour_id }}-{{ $today }}-{{ $field->id }}" class="form-control colorEditable">
                                <p class="msgError name_{{ $hour_id }}_{{ $today }}_{{ $field->id }}_error" style="margin:0;padding:0;color:red;font-weight:bold;"></p>
                                @error('name')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-4 col-12">
                            <div class="form-group">
                                <label for="phone" style="font-weight: bold;">Celular</label>
                                <input type="text" name="phone" id="phone-{{ $hour_id }}-{{ $today }}-{{ $field->id }}" 
                                       class="form-control colorEditable"
                                       maxlength="9"
                                       pattern="\d{9}"
                                       title="Ingrese 9 dígitos numéricos"
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 9)">
                                <p class="msgError phone_{{ $hour_id }}_{{ $today }}_{{ $field->id }}_error" style="margin:0;padding:0;color:red;font-weight:bold;"></p>
                                @error('phone')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>                        
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="modality-{{ $hour_id }}-{{ $today }}-{{ $field->id }}" style="font-weight: bold;">Modalidad</label>
                                <select name="modality" id="modality-{{ $hour_id }}-{{ $today }}-{{ $field->id }}" class="form-control colorEditable modality-select"
                                        data-hour="{{ $hour_id }}" data-date="{{ $today }}" data-field="{{ $field->id }}">
                                    <option value="7v7">7 vs 7</option>
                                    <option value="9v9">9 vs 9</option>
                                    <option value="11vs11">11 vs 11</option>
                                </select>
                            </div>
                        </div>
                    
                        <div class="col-md-6 col-12 juntar-con-container" id="juntar-con-container-{{ $hour_id }}-{{ $today }}-{{ $field->id }}" style="display: none;">
                            <div class="form-group">
                                <label style="font-weight: bold;">Juntar con</label>
                                <div class="juntar-checkboxes" id="juntar-checkboxes-{{ $hour_id }}-{{ $today }}-{{ $field->id }}">
                                    <!-- Checkboxes se llenan dinámicamente -->
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    

                    <div class="row mb-1 align-items-center">
                        <div class="col-auto">
                            <label for="nro_horas-{{ $hour_id }}-{{ $today }}-{{ $field->id }}" class="fw-bold">N° Horas</label>
                        </div>
                        <div class="col-auto">
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-stopwatch"></i>
                                </span>
                                <select class="cboNroHours form-control" id="nro_horas-{{ $hour_id }}-{{ $today }}-{{ $field->id }}" 
                                        data-hour="{{ $hour_id }}" data-date="{{ $today }}" data-field="{{ $field->id }}" 
                                        data-price="{{$first_hour >= $hourNight ? $field->night_price : $field->day_price}}" 
                                        name="nro_hours">
                                    <option value="0.5">0.5</option>
                                    <option value="1" selected>1</option>
                                    <option value="1.5">1.5</option>
                                    <option value="2">2</option>
                                    <option value="2.5">2.5</option>
                                    <option value="3">3</option>
                                    <option value="3.5">3.5</option>
                                    <option value="4">4</option>
                                    <option value="4.5">4.5</option>
                                    <option value="5">5</option>
                                    <option value="5.5">5.5</option>
                                    <option value="6">6</option>
                                </select>                                
                            </div>
                        </div>
                        <div class="col-auto">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="credit-{{ $hour_id }}-{{ $today }}-{{ $field->id }}" name="credit">
                                <label class="form-check-label" for="credit-{{ $hour_id }}-{{ $today }}-{{ $field->id }}">
                                    Crédito
                                </label>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <h5 class="card-title">Pagos</h5>
                        <div class="col-md-6 col-12">
                            <div class="form-group">
                                <label for="payment_type" style="font-weight: bold;">Tipo de pago</label>
                                <select name="payment_type" id="payment_type-{{ $hour_id }}-{{ $today }}-{{ $field->id }}" class="form-control colorEditable">
                                    <option value="EFECTIVO">EFECTIVO</option>
                                    <option value="YAPE">YAPE</option>
                                    <option value="PLIN">PLIN</option>
                                </select>
                                <p class="msgError payment_type_{{ $hour_id }}_{{ $today }}_{{ $field->id }}_error" style="margin:0;padding:0;color:red;font-weight:bold;"></p>
                                @error('payment_type')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="col-md-6 col-12">

                            <div class="form-group">
                                <label for="payment" style="font-weight: bold;">Pago</label>
                                <input value="{{$first_hour >= $hourNight?$field->night_price:$field->day_price}}" type="text" class="form-control colorEditable" name="payment" id="payment-{{ $hour_id }}-{{ $today }}-{{ $field->id }}" placeholder="0">
                                <p class="msgError payment_{{ $hour_id }}_{{ $today }}_{{ $field->id }}_error" style="margin:0;padding:0;color:red;font-weight:bold;"></p>
                                @error('payment')
                                    <div class="text-danger">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="form-group">
                            <label for="voucher" style="font-weight: bold;">Voucher</label>
                            <input type="file" class="form-control" name="voucher" id="voucher-{{ $hour_id }}-{{ $today }}-{{ $field->id }}">
                            @error('voucher')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <input type="hidden" name="schedule_id" value="{{ $schedule->id }}">
                    <input type="hidden" name="field_id" value="{{ $field->id }}">
                    <input type="hidden" name="date" value="{{ $today }}">         
                    <input type="hidden" name="reservation_type" id="reservation_type-{{ $hour_id }}-{{ $today }}-{{ $field->id }}">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary btn-save-booking" data-hour="{{ $hour_id }}" data-date="{{ $today }}" data-field="{{ $field->id }}">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>

    document.addEventListener("DOMContentLoaded", function () {
            
            setupModalFocus();
            setupSearchOnEnter();

    });

    function setupModalitySelect() {
    document.querySelectorAll(".modality-select").forEach(select => {
        select.addEventListener("change", function () {
            const hour = this.dataset.hour;
            const date = this.dataset.date;
            const fieldId = parseInt(this.dataset.field);
            const modalidad = this.value;
            let maxSelectable = 0;

            if (modalidad === '9v9') maxSelectable = 1;
            if (modalidad === '11vs11') maxSelectable = 2;

            const container = document.getElementById(`juntar-con-container-${hour}-${date}-${fieldId}`);
            const checkboxWrapper = document.getElementById(`juntar-checkboxes-${hour}-${date}-${fieldId}`);

            if (maxSelectable === 0) {
                container.style.display = 'none';
                checkboxWrapper.innerHTML = '';
                return;
            }
            

            // Mostrar contenedor antes de llenar
            container.style.display = 'block';
            checkboxWrapper.innerHTML = '<em>Cargando campos disponibles...</em>';

            // Hacer petición AJAX al backend
            fetch(`/reservas/available-fields?date=${date}&schedule_id=${hour}&nro_hours=1&exclude_field_id=${fieldId}`)
                .then(res => res.json())
                .then(data => {
                    if (!data.length) {
                        checkboxWrapper.innerHTML = '<em>No hay campos disponibles para juntar.</em>';
                        return;
                    }

                    const checkboxes = data.map(f => `
                        <li>
                            <div class="form-check">
                                <input class="form-check-input juntar-checkbox"
                                    type="checkbox"
                                    id="chk-${hour}-${date}-${fieldId}-${f.id}"
                                    name="juntar_con[]"
                                    data-price="${f.day_price}"
                                    value="${f.id}"
                                    data-group="${hour}-${date}-${fieldId}">
                                <label class="form-check-label" for="chk-${hour}-${date}-${fieldId}-${f.id}">
                                    ${f.field}
                                </label>
                            </div>
                        </li>
                    `).join('');

                    checkboxWrapper.innerHTML = checkboxes;

                    document.querySelectorAll(`.juntar-checkbox[data-group="${hour}-${date}-${fieldId}"]`).forEach(cb => {
                        cb.addEventListener('change', function () {
                            const group = this.dataset.group;

                            const selected = document.querySelectorAll(`.juntar-checkbox[data-group="${group}"]:checked`);
                            if (selected.length > maxSelectable) {
                                this.checked = false;
                                alert(`Solo puedes seleccionar hasta ${maxSelectable} campo(s) adicionales.`);
                                return;
                            }

                            updateJuntarConIds(group);

                            updateTotalPrice(group);
                        });
                    });
                })
                .catch(err => {
                    checkboxWrapper.innerHTML = '<em>Error al cargar los campos disponibles.</em>';
                    console.error(err);
                });
        });
    });
}



    // Enfocar input DNI al abrir modal
    function setupModalFocus() {
        document.querySelectorAll(".mdlBookingModal").forEach(modal => {
            modal.addEventListener("shown.bs.modal", function () {
                const hour = this.dataset.hour;
                const date = this.dataset.date;
                const field = this.dataset.field;

                const dniInput = document.getElementById(`document_number-${hour}-${date}-${field}`);
                if (dniInput) {
                    dniInput.focus();
                }
            });
        });
    }

    // Habilitar búsqueda por ENTER en inputs DNI y RUC
    function setupSearchOnEnter() {
        document.querySelectorAll("[name='document_number']").forEach(input => {
            input.addEventListener("keydown", function (event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    const button = this.closest(".input-group").querySelector(".btn-search");
                    if (button) button.click();
                }
            });
        });

        document.querySelectorAll("[name='ruc_number']").forEach(input => {
            input.addEventListener("keydown", function (event) {
                if (event.key === "Enter") {
                    event.preventDefault();
                    const button = this.closest(".input-group").querySelector(".btn-search-ruc");
                    if (button) button.click();
                }
            });
        });
    }

    function updateTotalPrice(group) {
    const checkbox = document.querySelector(`.juntar-checkbox[data-group="${group}"]`);
    if (!checkbox) return;

    const modal = checkbox.closest('.modal');
    if (!modal) return;

    const hour = modal.dataset.hour;
    const date = modal.dataset.date;
    const fieldId = modal.dataset.field;

    const basePriceElement = modal.querySelector(`#base-price-${hour}-${date}-${fieldId}`);
    const nroHorasInput = modal.querySelector(`#nro_horas-${hour}-${date}-${fieldId}`);
    const paymentInput = modal.querySelector(`#payment-${hour}-${date}-${fieldId}`);
    const creditCheckbox = modal.querySelector(`#credit-${hour}-${date}-${fieldId}`);

    const nroHoras = parseFloat(nroHorasInput?.value || 1);
    const basePrice = parseFloat(basePriceElement?.dataset.price || 0);

    let total = basePrice;

    // Sumar precios de campos adicionales seleccionados
    modal.querySelectorAll(`.juntar-checkbox[data-group="${group}"]:checked`).forEach(cb => {
        const fieldPrice = parseFloat(cb.dataset.price || 0);
        total += fieldPrice;
    });

    total *= nroHoras;

    // Actualiza visual del total arriba
    if (basePriceElement) basePriceElement.innerText = `Total S/. ${total.toFixed(2)}`;

    // Si es crédito, mostrar total calculado pero no permitir pagar
    if (creditCheckbox && creditCheckbox.checked) {
        if (paymentInput) {
            paymentInput.value = "0";
            paymentInput.setAttribute("readonly", true);
        }
    } else {
        if (paymentInput) {
            paymentInput.value = total.toFixed(2);
            paymentInput.removeAttribute("readonly");
        }
    }
}


function updateJuntarConIds(group) {
    const selected = document.querySelectorAll(`.juntar-checkbox[data-group="${group}"]:checked`);
    const ids = Array.from(selected).map(cb => cb.value);
    const parts = group.split('-');
    const hour = parts[0];
    const fieldId = parts[parts.length - 1];
    const date = parts.slice(1, -1).join('-');


    const hiddenInput = document.getElementById(`juntar_con_ids-${hour}-${date}-${fieldId}`);
    if (hiddenInput) {
        hiddenInput.value = ids.join(',');
        console.log('🔁 juntar_con_ids actualizado:', hiddenInput.value);
    } else {
        console.warn('No se encontró el input hidden para juntar_con_ids');
    }
}




    
</script>
