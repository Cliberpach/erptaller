import { app } from "./states";

export function loadTomSelect() {

    const initialCustomer = app.customerFormatted;
    window.clientSelect = new TomSelect('#customer_id', {
        valueField: 'id',
        options: [initialCustomer],
        items: [initialCustomer.id],
        labelField: 'full_name',
        searchField: ['full_name'],
        plugins: ['clear_button'],
        placeholder: 'Seleccione un cliente',
        maxOptions: 20,
        create: false,
        preload: false,
        onType: (str) => {
            lastCustomerQuery = str;
        },
        load: async (query, callback) => {
            if (query.length < 3) return callback();
            try {
                const url = `{{ route('tenant.utils.searchCustomer') }}?q=${encodeURIComponent(query)}`;
                const response = await fetch(url);
                if (!response.ok) throw new Error('Error al buscar clientes');
                const data = await response.json();
                const results = data.data ?? [];
                callback(results);
                if (results.length === 0) {
                    customerParams.documentSearchCustomer = lastCustomerQuery;
                    console.log("No se encontró en BD. Guardado:", window.typedCustomer);
                }
            } catch (error) {
                console.error('Error cargando clientes:', error);
                callback();
            }
        },
        render: {
            option: (item, escape) => `
                        <div>
                            <strong>${escape(item.full_name)}</strong><br>
                            <small>${escape(item.email ?? '')}</small>
                        </div>
                    `,
            item: (item, escape) => `<div>${escape(item.full_name)}</div>`,
            no_results: function (data, escape) {
                return `
                            <div class="no-results">
                                <i class="fas fa-search" style="margin-right:6px; color:#17a2b8;"></i>
                                Sin resultados
                            </div>
                        `;
            }
        }
    });

    window.vehicleSelect = new TomSelect('#vehicle_id', {
        valueField: 'id',
        labelField: 'text',
        searchField: ['text'],
        plugins: ['clear_button'],
        placeholder: 'Seleccione un vehículo',
        maxOptions: 20,
        create: false,
        preload: false,
        onType: (str) => {
            lastVehicleQuery = str;
        },
        load: async (query, callback) => {
            if (!query.length) return callback();
            try {
                const url = route('tenant.utils.searchVehicle', {
                    q: query,
                    customer_id: window.clientSelect.getValue()
                });

                const response = await fetch(url);
                if (!response.ok) throw new Error('Error al buscar vehiculos');
                const data = await response.json();
                const results = data.data ?? [];
                callback(results);
                if (results.length === 0) {
                    vehicleParams.plateSearchVehicle = lastVehicleQuery;
                    console.log("No se encontró en BD. Guardado:", window.typedCustomer);
                }
            } catch (error) {
                console.error('Error cargando vehiculos:', error);
                callback();
            }
        },
        render: {
            option: (item, escape) => `
                        <div>
                            <i class="fas fa-car" style="margin-right:6px; color:#0d6efd;"></i>
                            <strong>${escape(item.text)}</strong><br>
                            <small>${escape(item.subtext ?? '')}</small>
                        </div>
                    `,
            item: (item, escape) => `
                            <div>
                                <i class="fas fa-car" style="margin-right:6px; color:#0d6efd;"></i>
                                ${escape(item.text)}
                            </div>
                        `,
            no_results: function (data, escape) {
                return `
                            <div class="no-results">
                                <i class="fas fa-search" style="margin-right:6px; color:#17a2b8;"></i>
                                Sin resultados
                            </div>
                        `;
            }
        }
    });
}
