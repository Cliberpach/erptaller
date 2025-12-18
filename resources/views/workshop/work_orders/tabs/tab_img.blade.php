<div id="vehicleImagesCollapse">

    <h6 class="fw-bold text-secondary mb-3 mt-2">
        Subir imágenes del vehículo (Máx: 5)
    </h6>

    <div class="row g-3">

        @for ($i = 1; $i <= 5; $i++)
            <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12">
                <label class="form-label small fw-bold">Imagen {{ $i }}</label>
                <input accept="image/jpeg" data-max-files="1" data-allow-reorder="true" data-max-file-size="3MB"
                    type="file" name="vehicle_images[]" accept="image/*" class="filepond-input">
            </div>
        @endfor

    </div>

</div>
