<form action="" id="frmConfiguration">
    @foreach ($configuration as $item)
        <div class="row mb-4">
            <div class="col-lg-6 col-md-6 col-sm-6 d-flex align-items-center">
                <label for="configuration_{{ $item->id }}" style="font-weight: bold;">
                    {{ $item->description }}
                </label>
            </div>

            <div class="col-lg-6 col-md-6 col-sm-6">
                <x-toggle-switch-1 id="configuration_{{ $item->id }}" name="configuration_{{ $item->id }}"
                    :checked="$item->property == 1" />


                <p class="configuration_{{ $item->id }}_error msgError"></p>

            </div>
        </div>
    @endforeach
</form>
