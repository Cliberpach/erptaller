<form action="" method="post" id="form_edit_billing">
    @csrf
    <div class="row">
        

        <div class="col-12"></div>
        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-3">
            <label style="font-weight: bold;" for="urbanization" class="required_field">URBANIZACIÓN</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fas fa-city"></i>
                </span>
                <input value="{{$company_invoice->urbanization}}" required id="urbanization" name="urbanization" type="text" class="form-control" placeholder="URBANIZACIÓN" aria-label="Username" aria-describedby="basic-addon1">
            </div>
        </div>
        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-3">
            <label style="font-weight: bold;" for="local_code" class="required_field">CÓDIGO DE LOCAL</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fas fa-industry"></i>
                </span>
                <input value="{{$company_invoice->local_code}}" required id="local_code" name="local_code" type="text" class="form-control" placeholder="CÓDIGO DE LOCAL" aria-label="Username" aria-describedby="basic-addon1">
            </div>
        </div>
        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-3">
            <label style="font-weight: bold;" for="sol_user" class="required_field">USUARIO SOL</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fas fa-user-lock"></i>
                </span>
                <input  value="{{$company_invoice->secondary_user}}" required id="sol_user" name="sol_user" type="text" class="form-control" placeholder="USUARIO SOL" aria-label="Username" aria-describedby="basic-addon1">
            </div>
        </div>
        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-3">
            <label style="font-weight: bold;" for="sol_pass" class="required_field">CLAVE SOL</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fas fa-key"></i>
                </span>
                <input value="{{$company_invoice->secondary_password}}" required id="sol_pass" name="sol_pass" type="text" class="form-control" placeholder="CLAVE SOL" aria-label="Username" aria-describedby="basic-addon1">
            </div>
        </div>

        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-3">
            <label style="font-weight: bold;" for="api_user_gre" class="">USUARIO GUÍAS REMISIÓN</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fas fa-user-lock"></i>
                </span>
                <input value="{{$company_invoice->api_user_gre}}" id="api_user_gre" name="api_user_gre" type="text" class="form-control" placeholder="USUARIO GUÍAS REMISIÓN" aria-label="Username" aria-describedby="basic-addon1">
            </div>
        </div>
        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 mb-3">
            <label style="font-weight: bold;" for="api_pass_gre" class="">CLAVE GUÍAS REMISIÓN</label>
            <div class="input-group">
                <span class="input-group-text" id="basic-addon1">
                    <i class="fas fa-key"></i>
                </span>
                <input value="{{$company_invoice->api_password_gre}}" id="api_pass_gre" name="api_pass_gre" type="text" class="form-control" placeholder="CLAVE GUÍAS REMISIÓN" aria-label="Username" aria-describedby="basic-addon1">
            </div>
        </div>

        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <label style="font-weight: bold;" class="required_field" for="certificate">CERTIFICADO</label>
            <input accept=".pem" class="form-control form-control" id="certificate" name="certificate" type="file">
            <span style="display:block;color:black;">
                @if ($company_invoice->certificate)
                    {{$company_invoice->certificate}}
                @else
                    SIN CERTIFICADO
                @endif
            </span>
            <span style="color: blue; font-style: italic;">(PEM)</span>
        </div>

    </div>
</form>