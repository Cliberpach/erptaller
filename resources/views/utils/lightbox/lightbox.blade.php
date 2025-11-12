
<style>

/* Lightbox container */
#lightbox {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.8);
    justify-content: center;
    align-items: center;
    z-index: 999999;
    opacity: 0;
    transition: opacity 0.5s ease;
}

/* Estilos para la imagen dentro del lightbox */
#lightbox_img {
    max-width: 90%;
    max-height: 90%;
    border-radius: 10px;
}

/* Estilos para el botón de cierre (la "X") */
#close_lightbox {
    position: absolute;
    top: 20px;
    right: 30px;
    font-size: 30px;
    font-weight: bold;
    color: white;
    cursor: pointer;
}

.imgShowLightBox{
    cursor: pointer;
}
</style>



<!-- Lightbox container -->
<div id="lightbox">
    <!-- Botón de cierre -->
    <span id="close_lightbox">&times;</span>
    <img id="lightbox_img">
</div>


<script>
    document.addEventListener('DOMContentLoaded',()=>{

        //========= MOSTRAR LA IMAGEN CLICKEADA EN EL LIGTHBOX =========
        document.addEventListener('click', function(e) {

            if(e.target.classList.contains('imgShowLightBox')){
                const imgSrc        = e.target.src;
                const lightbox      = document.getElementById('lightbox');
                const lightboxImg   = document.getElementById('lightbox_img');

                lightboxImg.src = imgSrc;
                lightbox.style.display = 'flex'; // Mostrar el lightbox como flex para centrar

                // Usar un pequeño retraso para que la transición de opacidad funcione
                setTimeout(function() {
                    lightbox.style.opacity = '1';  // Aplicar la transición de fade-in
                }, 10);
            }

        });

        //====== CERRAR LIGHTBOX AL CLICKEAR EN LA X ========
        document.getElementById('close_lightbox').addEventListener('click', function() {
            var lightbox = document.getElementById('lightbox');
            lightbox.style.opacity = '0';  // Iniciar la transición de fade-out

            // Esperar a que la transición termine antes de ocultar completamente el lightbox
            setTimeout(function() {
                lightbox.style.display = 'none';
            }, 500); // El tiempo debe coincidir con el de la transición
        });

        //======== CERRAR LIGHTBOX AL CLICKEAR EN EL FONDO OSCURO =========
        document.getElementById('lightbox').addEventListener('click', function(event) {
            if (event.target === this) {
                this.style.opacity = '0';  // Iniciar la transición de fade-out

                // Esperar a que la transición termine antes de ocultar completamente el lightbox
                setTimeout(function() {
                    document.getElementById('lightbox').style.display = 'none';
                }, 500); // El tiempo debe coincidir con el de la transición
            }
        });
    })
</script>
