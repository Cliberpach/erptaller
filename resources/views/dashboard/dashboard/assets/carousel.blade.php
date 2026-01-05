<style>
    .dashboard-card {
        background-color: #1e3a8a;
        color: white;
        padding: 20px;
        border-radius: 15px;
        border: 3px solid white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 0.9rem;
        transition: transform 0.3s ease-in-out,
            box-shadow 0.3s ease-in-out,
            background-color 0.3s ease-in-out;
        min-height: 140px;
        text-align: center;
        cursor: pointer;
        overflow: hidden;
        height: 100%;
        white-space: normal;
    }

    .dashboard-card div:first-child {
        font-size: 1.1rem;
        text-wrap: balance;
        text-align: center;
    }

    /* Hover */
    .dashboard-card:hover {
        transform: scale(1.01);
        box-shadow: 0 8px 18px rgba(0, 0, 0, 0.2);
        background-color: #2563eb;
        border-color: #2563eb;
    }

    /* Protege el carousel del overflow */
    .carousel {
        overflow-x: hidden;
    }

    /* Flechas */
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        padding: 15px;
    }

    /* Flechas DENTRO del contenedor (clave) */
    .carousel-control-prev,
    .carousel-control-next {
        width: 5%;
    }

    .carousel-control-prev {
        left: 0;
    }

    .carousel-control-next {
        right: 0;
    }

    /* Indicadores */
    .carousel-indicators [data-bs-target] {
        background-color: #1e3a8a;
    }

    .carousel-indicators .active {
        background-color: #2563eb;
    }
</style>


<div id="carouselExampleIndicators" class="carousel slide">

    <div class="carousel-indicators">
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active"
            aria-current="true"></button>
        <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1"></button>
    </div>

    <div class="carousel-inner">
        <div class="carousel-item active">
            <div class="row g-3 align-items-stretch">
                <div class="col-lg-3 col-md-3 col-sm-6 col-12">
                    <div class="dashboard-card">
                        <div>VENTAS DEL MES</div>
                        <p id="ventas_mes">S/0.00</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-12">
                    <div class="dashboard-card">
                        <div>COMPRAS DEL MES</div>
                        <p id="compras_mes">S/0.00</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-12">
                    <div class="dashboard-card">
                        <div>UTILIDAD BRUTA MES</div>
                        <p id="utilidad_bruta_mes">S/0.00</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-12">
                    <div class="dashboard-card">
                        <div>IGV DEL MES</div>
                        <p id="igv_mes">S/0.00</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="carousel-item">
            <div class="row g-3 align-items-stretch">
                <div class="col-lg-3 col-md-3 col-sm-6 col-12">
                    <div class="dashboard-card">
                        <div>CANT COMPROBANTES MES</div>
                        <p id="comprobantes_mes">0</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-12">
                    <div class="dashboard-card">
                        <div>TOTAL BOLETAS MES</div>
                        <p id="boletas_mes">0</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-12">
                    <div class="dashboard-card">
                        <div>TOTAL FACTURAS MES</div>
                        <p id="facturas_mes">0</p>
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 col-sm-6 col-12">
                    <div class="dashboard-card">
                        <div>TOTAL NOTA CRÉDITO MES</div>
                        <p id="nota_credito_mes">0</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators"
        data-bs-slide="prev">
        <span class="carousel-control-prev-icon"></span>
        <span class="visually-hidden">Anterior</span>
    </button>

    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators"
        data-bs-slide="next">
        <span class="carousel-control-next-icon"></span>
        <span class="visually-hidden">Siguiente</span>
    </button>
</div>
