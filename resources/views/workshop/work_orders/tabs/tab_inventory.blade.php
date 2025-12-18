<!-- Contenido colapsable -->
<div id="inventoryCollapse">
    @foreach ($checks_inventory_vehicle as $category)
        <h6 class="fw-bold text-secondary mb-2 mt-3">
            {{ $category['category_name'] }}
        </h6>

        <div class="row g-2">
            @foreach ($category['items'] as $item)
                <div class="col-lg-3 col-md-4 col-sm-12">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="inventory_items[]" value="{{ $item['id'] }}"
                            id="inv_item_{{ $item['id'] }}">

                        <label class="form-check-label small" for="inv_item_{{ $item['id'] }}">
                            {{ $item['name'] }}
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

    <div class="row g-2 mt-2">
        <div class="col-lg-3 col-md-4 col-sm-12 col-xs-12">
            <label for="fuelSelect" class="form-check-label text-secondary fw-bold">NIVEL
                DE
                GASOLINA:</label>
            <select id="fuelSelect" class="form-control" name="fuel_level">
                <option value="-1">NA</option>
                <option value="0">VACIO</option>
                <option value="25">1/4</option>
                <option value="50" selected>1/2</option>
                <option value="75">3/4</option>
                <option value="100">LLENO</option>
            </select>

            <div style="width: 250px; margin-bottom: 20px;" class="mt-2">
                <div id="gauge"></div>
            </div>
        </div>
    </div>
</div>
