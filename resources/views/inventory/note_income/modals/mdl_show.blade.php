<style>
    .data-key {
      font-weight: bold !important;
      color: #007bff !important; 
      width: 160px;
      display: inline-block; 
    }
    .data-value {
      color: #343a40 !important; 
      word-break: break-word !important; 
    }
    .modal-body {
      background-color: #f8f9fa; 
    }
    .modal-body .col-12 {
      border: 1px solid #dee2e6;
      background-color: #ffffff; 
    }
</style>

<div class="modal fade" id="mdlShowNoteIncome" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">
                    NOTA INGRESO <span style="padding:0;margin:0;" id="spanNoteId"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div class="col-12 border rounded p-3 bg-light shadow-sm" style="margin-bottom: 10px;">
                  <p class="mb-2"><span class="data-key">ID:</span> <span class="data-value" id="noteId"></span></p>
                  <p class="mb-2"><span class="data-key">Usuario Registrador:</span> <span class="data-value" id="userRecorderName"></span></p>
                  <p class="mb-2"><span class="data-key">Observación:</span> <span class="data-value" id="observation"></span></p>
                  <p class="mb-2"><span class="data-key">Estado:</span> <span class="data-value" id="estado"></span></p>
                  <p class="mb-2"><span class="data-key">Creado el:</span> <span class="data-value" id="createdAt"></span></p>
                  <p class="mb-0"><span class="data-key">Actualizado el:</span> <span class="data-value" id="updatedAt"></span></p>
                </div>
                
                <div class="col-12 border rounded p-2 bg-light shadow-sm">
                    @include('inventory.note_income.tables.tbl_note_income_show')
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Salir</button>
            </div>
        </div>
    </div>
</div>


<script>

    let dtNoteIncomeShow    =   null;

    function openMdlShowNoteIncome(note_id) {

        getNoteIncome(note_id);
        
    }

    async function getNoteIncome(note_id){
        try {
            toastr.clear();
            mostrarAnimacion1();
            const token                         =   document.querySelector('input[name="_token"]').value;
            const urlGetNoteIncome              =   @json(route('tenant.inventarios.nota_ingreso.show', ['id' => 'ID']));
            const url                           =   urlGetNoteIncome.replace('ID', note_id);

            const response  =   await fetch(url, {
                                    method: 'GET',
                                    headers: {
                                        'X-CSRF-TOKEN': token 
                                    }
                                });

            const   res =   await response.json();

            if(res.success){
                paintNoteIncome(res.note_income);

                destroyDataTable(dtNoteIncomeShow);
                clearTable('tbl_note_income_show');
                paintNoteIncomeDetail(res.note_income_detail);
                dtNoteIncomeShow    =   loadDataTableSimple(dtNoteIncomeShow,'tbl_note_income_show');

                $('#mdlShowNoteIncome').modal('show');
                toastr.success('MOSTRANDO NOTA DE INGRESO');
            }else{
                toastr.error(res.message,'ERROR EN EL SERVIDOR');
            }

        } catch (error) {
            toastr.error(error,'ERROR EN LA PETICIÓN VER NOTA DE INGRESO');
        }finally{
            ocultarAnimacion1();
        }
    }

    function paintNoteIncome(noteIncome) {

        document.getElementById('spanNoteId').textContent = `#${noteIncome.id}`;
        document.getElementById('noteId').textContent = noteIncome.id;
        document.getElementById('userRecorderName').textContent = noteIncome.user_recorder_name;
        document.getElementById('observation').textContent = noteIncome.observation;
        document.getElementById('estado').textContent = noteIncome.estado;
        document.getElementById('createdAt').textContent = noteIncome.created_at;
        document.getElementById('updatedAt').textContent = noteIncome.updated_at;

    }

    function paintNoteIncomeDetail(details) {
        
        const tbody = document.querySelector("#tbl_note_income_show tbody");

        details.forEach(detail => {
            const row = document.createElement("tr");
            
            row.innerHTML = `
                <td>${detail.product_name}</td>
                <td>${detail.category_name}</td>
                <td>${detail.brand_name}</td>
                <td>${detail.quantity}</td>
            `;

            tbody.appendChild(row); 
        });

    }



</script>