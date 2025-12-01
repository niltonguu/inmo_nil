<?php // app/Views/proyectos/modals/generar_manzanas.php ?>
<div class="modal fade" id="modalGenerarManzanas" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="formGenerarManzanas" class="modal-content" method="post" action="index.php?c=proyectos&a=manzanas_generate" onsubmit="return false;">

      <div class="modal-header">
        <h5 class="modal-title">Generar manzanas</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div class="mb-2">
          <label class="form-label">Etapa</label>
          <select name="id_etapa" id="gen_manzanas_id_etapa" class="form-select" required></select>
        </div>

        <div class="row g-2">
          <div class="col">
            <label class="form-label">Letra inicio</label>
            <input type="text" name="letra_inicio" id="gen_manzanas_letra_inicio" class="form-control" maxlength="1" value="A">
          </div>
          <div class="col">
            <label class="form-label">Letra fin</label>
            <input type="text" name="letra_fin" id="gen_manzanas_letra_fin" class="form-control" maxlength="1" value="H">
          </div>
        </div>

        <p class="small text-muted mb-0 mt-2">
          Ejemplo: de A a H generará manzanas A, B, C, D, E, F, G, H.
        </p>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" id="btnGenerateManzanas" class="btn btn-primary btn-sm">Generar</button>
      </div>

    </form>
  </div>
</div>
