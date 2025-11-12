<form action="" id="frmConfiguration">
    @foreach ($configuration as $item)
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12" style="display: flex;align-items:center;">
                <label style="font-weight: bold;" for="configuration_{{$item->id}}">{{$item->description}}</label>
            </div>
            @if ($item->id === 1)
                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                    <input required value="{{$item->property}}" id="configuration_{{$item->id}}" name="configuration_{{$item->id}}" type="time" class="form-control">
                    <p style="margin:0;padding:0;color:red;font-weight:bold;" class="configuration_{{$item->id}}_error"></p>
                </div>
            @endif
        </div>
    @endforeach
</form>  