  <form id="createCategoryForm" method="POST">
      @csrf
      <div class="row">
          <div class="form-group">
              <label for="description" class="form-label">Nombre <span>*</span></label>
              <input required id="name" name="name" type="text" class="form-control inputName"
                  placeholder="Nombre de la categoría" oninput="this.value = this.value.toUpperCase()">
              <p class="msgError name_error" style="font-weight: bold;color:rgb(208, 11, 11);"></p>
          </div>
      </div>
  </form>
