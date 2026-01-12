<form action="" id="formActualizarUsuario" method="post">
    <div class="row">
        @csrf
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label for="colaborador" class="required_field" style="font-weight: bold;">Colaborador</label>
            <select required name="colaborador" required class="form-select select_2_form" id="colaborador" data-placeholder="Seleccionar">
                <option></option>
                @foreach ($collaborators as $colaborador)
                    <option
                    @if ($colaborador->id === $user->collaborator_id)
                        selected
                    @endif
                    value="{{$colaborador->id}}">
                        {{$colaborador->full_name.' - '.$colaborador->document_type_abbreviation.':'.$colaborador->document_number}}
                    </option>
                @endforeach

            </select>
            <span class="colaborador_error msgError"  style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label for="correo" class="required_field" style="font-weight: bold;">Correo</label>
            <input value="{{$user->email}}" maxlength="255" name="correo" id="correo" required type="email" class="form-control" placeholder="Correo">
            <span class="correo_error msgError"  style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label for="password" class="required_field" style="font-weight: bold;">Contraseña</label>
            <div class="input-group mb-3">
                <button style="width:50px;" class="btn btn-primary btn_ver_password password_oculto" type="button" id="button-addon1">
                    <i class="fa-solid fa-eye-slash"></i>
                </button>
                <input value="{{$user->password_visible}}" maxlength="50" type="password" id="password" name="password" class="form-control" placeholder="" aria-label="Example text with button addon" aria-describedby="button-addon1">
            </div>
            <span class="password_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label for="repetir_password" class="required_field" style="font-weight: bold;">Repetir contraseña</label>
            <div class="input-group mb-3">
                <button style="width:50px;"  class="btn btn-primary btn_ver_repetir_password password_oculto" type="button" id="button-addon1">
                    <i class="fa-solid fa-eye-slash"></i>
                </button>
                <input value="{{$user->password_visible}}" maxlength="50" type="password" id="repetir_password" name="repetir_password" class="form-control" placeholder="" aria-label="Example text with button addon" aria-describedby="button-addon1">
            </div>
            <span class="repetir_password_error msgError" style="color:red;"></span>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 pb-2">
            <label for="rol" class="required_field" style="font-weight: bold;">Rol</label>
            <select required name="rol" required class="form-select select_2_form" id="rol" data-placeholder="Seleccionar">
                <option></option>
                @foreach ($roles as $rol)
                    <option
                    @if ($role->id === $rol->id)
                        selected
                    @endif
                    value="{{$rol->id}}">
                        {{$rol->name}}
                    </option>
                @endforeach
            </select>
            <span class="rol_error msgError"  style="color:red;"></span>
        </div>
    </div>
</form>
