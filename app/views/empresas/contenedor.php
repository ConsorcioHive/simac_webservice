<?php
// app/views/empresas/contenedor.php
// Contenedor de archivos de empresa (gestor de carpetas y archivos)
require APP_PATH . '/views/layout/app_start.php';
?>
<div class="container-fluid">
  <div class="row">
    <div class="col-12">
      <div class="card">
        <div class="card-header">
          <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Contenedor de archivos - <?= htmlspecialchars($empresa['nombre'] ?? '') ?></h5>
            <a href="index.php?page=empresas_form" class="btn btn-outline-primary btn-sm"><i class="fa fa-arrow-left me-1"></i>Volver a empresa</a>
          </div>
        </div>
        <div class="card-body">
          <!-- Navegación breadcrumb -->
          <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0">
              <li class="breadcrumb-item"><a href="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?>">Raíz</a></li>
              <?php foreach ($rutaNavegacion as $f): ?>
              <li class="breadcrumb-item"><a href="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?>&folder_id=<?= (int)$f['id'] ?>"><?= htmlspecialchars($f['nombre']) ?></a></li>
              <?php endforeach; ?>
            </ol>
          </nav>

          <!-- Acciones: crear carpeta + subir archivos -->
          <div class="row g-2 mb-3">
            <div class="col-md-6">
              <form method="POST" action="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?><?= $currentFolderId ? '&folder_id=' . (int)$currentFolderId : '' ?>">
                <input type="hidden" name="action" value="crear_carpeta">
                <label class="form-label small"><i class="fa fa-folder"></i> Nueva carpeta</label>
                <div class="input-group">
                  <input type="text" class="form-control" name="folder_name" placeholder="Nombre de la carpeta" required>
                  <button type="submit" class="btn btn-primary"><i class="fa fa-plus"></i> Crear</button>
                </div>
              </form>
            </div>
            <div class="col-md-6">
              <form method="POST" action="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?><?= $currentFolderId ? '&folder_id=' . (int)$currentFolderId : '' ?>" enctype="multipart/form-data">
                <input type="hidden" name="action" value="subir_archivos">
                <label class="form-label small"><i class="fa fa-upload"></i> Subir archivos</label>
                <div class="input-group">
                  <input type="file" class="form-control" name="files[]" multiple accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.zip" required>
                  <button type="submit" class="btn btn-success"><i class="fa fa-upload"></i> Subir</button>
                </div>
              </form>
            </div>
          </div>

          <!-- Listado de carpetas -->
          <?php if (!empty($carpetas)): ?>
          <h6 class="mb-2"><i class="fa fa-folder me-1"></i>Carpetas</h6>
          <div class="row g-3 mb-4">
            <?php foreach ($carpetas as $folder): ?>
            <div class="col-6 col-sm-4 col-md-3 col-lg-2">
              <div class="card text-center h-100">
                <div class="card-body py-3">
                  <a href="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?>&folder_id=<?= (int)$folder['id'] ?>" style="text-decoration: none; color: inherit;">
                    <i class="fa fa-folder fa-3x text-warning mb-2"></i>
                    <p class="mb-0 small text-truncate"><?= htmlspecialchars($folder['nombre']) ?></p>
                  </a>
                  <form method="POST" action="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?><?= $currentFolderId ? '&folder_id=' . (int)$currentFolderId : '' ?>" class="mt-2" onsubmit="return confirm('¿Eliminar carpeta y su contenido?');">
                    <input type="hidden" name="action" value="eliminar">
                    <input type="hidden" name="item_id" value="<?= (int)$folder['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm w-100"><i class="fa fa-trash"></i></button>
                  </form>
                </div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>

          <!-- Listado de archivos -->
          <?php if (!empty($archivos)): ?>
          <h6 class="mb-2"><i class="fa fa-file me-1"></i>Archivos</h6>
          <div class="table-responsive">
            <table class="table table-hover">
              <thead class="table-light">
                <tr><th>Nombre</th><th>Tipo</th><th>Tamaño</th><th class="text-end">Acciones</th></tr>
              </thead>
              <tbody>
                <?php foreach ($archivos as $file): ?>
                <tr>
                  <td>
                    <i class="fa fa-file-<?= in_array($file['extension'], ['pdf']) ? 'pdf text-danger' : (in_array($file['extension'], ['jpg','jpeg','png','gif','webp']) ? 'image text-success' : ' text-secondary') ?> me-2"></i>
                    <?= htmlspecialchars($file['nombre']) ?>
                  </td>
                  <td><span class="badge badge-light-secondary"><?= htmlspecialchars($file['extension'] ?: 'UNK') ?></span></td>
                  <td><?= $file['size_bytes'] ? number_format($file['size_bytes']) . ' bytes' : '—' ?></td>
                  <td class="text-end text-nowrap">
                    <?php if (!$file['is_folder']): ?>
                    <a class="btn btn-outline-primary btn-sm" href="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?><?= $currentFolderId ? '&folder_id=' . (int)$currentFolderId : '' ?>&action=descargar&file_id=<?= (int)$file['id'] ?>"><i class="fa fa-download"></i></a>
                    <?php endif; ?>
                    <form method="POST" action="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?><?= $currentFolderId ? '&folder_id=' . (int)$currentFolderId : '' ?>" style="display:inline;" onsubmit="return confirm('¿Eliminar?');">
                      <input type="hidden" name="action" value="eliminar">
                      <input type="hidden" name="item_id" value="<?= (int)$file['id'] ?>">
                      <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i></button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>

          <?php if (empty($carpetas) && empty($archivos)): ?>
          <p class="text-muted text-center my-4"><em>Esta carpeta está vacía.</em></p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require APP_PATH . '/views/layout/app_end.php'; ?>
        <div class="row">
            <div class="col-xl-3 box-col-6 pe-0">
                <div class="file-sidebar">
                    <div class="card">
                        <div class="card-body">
                            <ul>
                                <li>
                                    <div class="btn btn-primary"><i data-feather="home"> </i>Inincio </div>
                                </li>
                                <li>
                                    <div class="btn btn-light"><i data-feather="folder"></i>Ver Todos </div>
                                </li>
                               
                            </ul>
                            <hr>
                            <ul>
                                <li>
                                    <div class="btn btn-outline-primary"><i data-feather="database"> </i>Consumido/Unidades
                                    </div>
                                    <div class="m-t-15">
                                        <div class="progress sm-progress-bar mb-1">
                                            <div class="progress-bar bg-primary" role="progressbar"
                                                style="width: 25%" aria-valuenow="25" aria-valuemin="0"
                                                aria-valuemax="100"></div>
                                        </div>
                                        <p>25 GB of 100 GB used</p>
                                    </div>
                                </li>
                            </ul>
                            <hr>
                            <ul>
                                <li>
                                    <a href="index.php?page=empresas_lista&id=<?= (int)$empresa['id']; ?>" 
                                       class="btn btn-outline-danger">
                                        <i data-feather="grid"></i> Regresar
                                    </a>

                                </li>
                                
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-9 col-md-12 box-col-12">
                <div class="file-content">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-header">
                              <div class="d-flex align-items-center gap-2" style="flex-wrap: nowrap; overflow-x: auto;">
                                <!-- Search -->
                                <form class="form-inline d-flex align-items-center gap-1" action="#" method="get" style="flex-shrink: 0;">
                                  <i class="fa fa-search f-18"></i>
                                  <input class="form-control-plaintext border-bottom" type="text" placeholder="Search..." style="width: 110px; font-size: 0.95rem;">
                                  <button type="submit" class="btn btn-sm btn-link p-0">
                                    <i class="fa fa-arrow-right text-primary f-16"></i>
                                  </button>
                                </form>
                            
                                <div style="width: 1px; height: 30px; background-color: #ddd; flex-shrink: 0;"></div>
                            
                                <!-- Form crear carpeta -->
                                <form class="d-flex align-items-center gap-1"
                                      action="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?><?= $currentFolderId ? '&folder_id=' . (int)$currentFolderId : '' ?>"
                                      method="POST" style="flex-shrink: 0;">
                                  <input type="hidden" name="action" value="crear_carpeta">
                                  <i class="fa fa-folder-o f-16"></i>
                                  <input type="text" name="folder_name"
                                         class="form-control-plaintext border-bottom"
                                         placeholder="Carpeta" required style="width: 110px; font-size: 0.9rem;">
                                  <button type="submit" class="btn btn-sm btn-link p-0">
                                    <i class="fa fa-plus f-16 text-primary"></i>
                                  </button>
                                </form>
                            
                                <div style="width: 1px; height: 30px; background-color: #ddd; flex-shrink: 0;"></div>
                            
                                <!-- Form subir archivos -->
                                <form class="d-flex align-items-center gap-1"
                                      action="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?><?= $currentFolderId ? '&folder_id=' . (int)$currentFolderId : '' ?>"
                                      method="POST"
                                      enctype="multipart/form-data" style="flex-shrink: 0;">
                                  <input type="hidden" name="action" value="subir_archivos">
                                  <i class="fa fa-file-o f-16"></i>
                                  <label class="form-control-plaintext border-bottom mb-0" style="cursor: pointer; width: 110px; display: block; font-size: 0.9rem;">
                                    <input type="file" name="files[]" multiple class="d-none" id="file-input"
                                           onchange="document.getElementById('file-label').textContent = this.files.length + ' archivo(s)'">
                                    <span id="file-label">Archivos</span>
                                  </label>
                                  <button type="submit" class="btn btn-sm btn-link p-0">
                                    <i class="fa fa-upload f-16 text-primary"></i>
                                  </button>
                                </form>
                              </div>
                            </div>




                        
                        <!-- Breadcrumb de navegación -->
                        <nav aria-label="breadcrumb" class="mb-3">
                          <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                              <a href="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?>">
                                <?= htmlspecialchars($empresa['nombre'] ?? 'Raíz', ENT_QUOTES, 'UTF-8') ?>
                              </a>
                            </li>
                            <?php if (!empty($breadcrumb)): ?>
                              <?php foreach ($breadcrumb as $index => $item): ?>
                                <?php $isLast = ($index === count($breadcrumb) - 1); ?>
                                <li class="breadcrumb-item <?= $isLast ? 'active' : '' ?>">
                                  <?php if (!$isLast): ?>
                                    <a href="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?>&folder_id=<?= (int)$item['id'] ?>">
                                      <?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                  <?php else: ?>
                                    <?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                  <?php endif; ?>
                                </li>
                              <?php endforeach; ?>
                            <?php endif; ?>
                          </ol>
                        </nav>
                        

                        <div class="card-body file-manager">
                            <h6 class="mt-4">Folders</h6>
                            <ul class="folder">
                              <?php if (!empty($foldersRoot)): ?>
                                <?php foreach ($foldersRoot as $folder): ?>
                                  <li class="folder-box">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                      <a href="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?>&folder_id=<?= (int)$folder['id'] ?>"
                                         style="text-decoration: none; color: inherit; flex: 1;">
                                        <div class="media">
                                          <i class="fa fa-folder f-36 txt-warning"></i>
                                          <div class="media-body ms-3">
                                            <h6 class="mb-0">
                                              <?= htmlspecialchars($folder['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                            </h6>
                                            <p></p>
                                          </div>
                                        </div>
                                      </a>
                                      <form method="POST" action="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?><?= $currentFolderId ? '&folder_id=' . (int)$currentFolderId : '' ?>" style="display:inline;">
                                        <input type="hidden" name="action" value="eliminar">
                                        <input type="hidden" name="item_id" value="<?= (int)$folder['id'] ?>">
										
                                        
                                        <button type="submit" class="btn btn-sm btn-link text-danger p-0" 
                                        onclick="return confirm('¿Eliminar esta carpeta y todo su contenido?');" 
                                        title="Eliminar carpeta">
                                          <i class="fa fa-trash f-20"></i>
                                        </button>


                                      </form>
                                    </div>
                                  </li>
                                <?php endforeach; ?>
                              <?php else: ?>
                                <li>
                                  <p class="text-muted mb-0">No hay carpetas registradas aún.</p>
                                </li>
                              <?php endif; ?>
                            </ul>


                            <h6 class="mt-4">Files</h6>
                            <ul class="files">
                              <?php if (!empty($filesCurrent)): ?>
                                  
                                <?php foreach ($filesCurrent as $file): ?>                                
                                  <li class="file-box">
                                      <div class="file-top">
                                        <i class="fa <?= !empty($file['extension']) ? \App\Models\CompanyFiles::obtenerIconoPorExtension($file['extension']) : 'fa-file-o txt-muted' ?>"></i>
                                        <div class="dropdown">
                                          <button class="btn btn-sm btn-link" type="button" data-bs-toggle="dropdown">
                                            <i class="fa fa-ellipsis-v f-14"></i>
                                          </button>
                                          <?php
											$pathRel = $file['path_relativo'] ?? '';           // ej: /uploads/empresas/...
											// Asegurar que empiece por /public/ porque físicamente está en app.representacionesmedicary.com/public/...
											if (strpos($pathRel, '/public/') !== 0) {
												$pathRel = '/public' . $pathRel;              // quedará: /public/uploads/empresas/...
											}
											$rutaFisica    = $_SERVER['DOCUMENT_ROOT'] . $pathRel;
											$extension     = strtolower($file['extension'] ?? '');
											$esImagen      = in_array($extension, ['jpg','jpeg','png','gif','webp']);
											$existeArchivo = is_file($rutaFisica);
											?>
											<ul class="dropdown-menu">
                                              <li>
                                                <a class="dropdown-item" href="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?><?= $currentFolderId ? '&folder_id=' . (int)$currentFolderId : '' ?>&action=descargar&file_id=<?= (int)$file['id'] ?>">
                                                  <i class="fa fa-download"></i> Descargar
                                                </a>
                                              </li>
                                              <li>
                                                <button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#modalMover" 
                                                        onclick="document.getElementById('modalItemId').value = <?= (int)$file['id'] ?>;">
                                                  <i class="fa fa-arrows"></i> Mover
                                                </button>
                                              </li>
                                            
                                              <?php if ($esImagen && $existeArchivo): ?>
                                                <li>
                                                  <button class="dropdown-item"
                                                          type="button"
                                                          data-bs-toggle="modal"
                                                          data-bs-target="#modalPreviewImagen"
                                                          data-file-url="<?= htmlspecialchars($pathRel, ENT_QUOTES, 'UTF-8') ?>">
                                                    <i class="fa fa-eye"></i> Mostrar
                                                  </button>
                                                </li>
                                              <?php elseif ($esImagen && !$existeArchivo): ?>
                                                <li>
                                                  <form method="POST"
                                                        action="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?><?= $currentFolderId ? '&folder_id=' . (int)$currentFolderId : '' ?>"
                                                        onsubmit="return confirm('El archivo físico no existe.\n¿Desea eliminar este registro de la base de datos?');">
                                                    <input type="hidden" name="action" value="eliminar">
                                                    <input type="hidden" name="item_id" value="<?= (int)$file['id'] ?>">
                                                    <button type="submit" class="dropdown-item text-warning">
                                                      <i class="fa fa-exclamation-triangle"></i> Eliminar registro huérfano
                                                    </button>
                                                  </form>
                                                </li>
                                              <?php endif; ?>
                                            
                                              <li><hr class="dropdown-divider"></li>
                                              <li>
                                                <form method="POST" action="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?><?= $currentFolderId ? '&folder_id=' . (int)$currentFolderId : '' ?>" style="display:inline;">
                                                  <input type="hidden" name="action" value="eliminar">
                                                  <input type="hidden" name="item_id" value="<?= (int)$file['id'] ?>">
                                                  <button type="submit" class="dropdown-item text-danger" onclick="return confirm('¿Eliminar este archivo?');">
                                                    <i class="fa fa-trash"></i> Eliminar
                                                  </button>
                                                </form>
                                              </li>
                                            </ul>



                                        </div>
                                      </div>
                                      <div class="file-bottom">
                                        <h6><?= htmlspecialchars($file['nombre'], ENT_QUOTES, 'UTF-8') ?></h6>
                                        <p class="mb-1">
                                          <?= number_format(($file['size_bytes'] ?? 0) / 1024, 2) ?> KB
                                        </p>
                                      </div>
                                    </li>

                                <?php endforeach; ?>
                              <?php else: ?>
                                <li>
                                  <p class="text-muted mb-0">No hay archivos en esta carpeta.</p>
                                </li>
                              <?php endif; ?>
                            </ul>

                        </div>
                    </div>
                </div>
            </div>
        </div>
     </div>

    </div> <!-- .page-body -->
  </div>   <!-- .page-body-wrapper -->
</div>     <!-- #pageWrapper -->
<div class="icon-hover-bottom p-fixed fa-fa-icon-show-div opecity-0">
    <div class="container-fluid">
        <div class="row">
            <div class="icon-popup">
                <div class="close-icon"><i class="icofont icofont-close"></i></div>
                <div class="icon-first"><i id="icon_main"></i></div>
                <div class="icon-class">
                    <label class="icon-title">data-feather</label><span id="fclass1"></span>
                </div>
                <div class="icon-last icon-last">
                    <label class="icon-title">Markup</label>
                    <div class="form-inline">
                        <div class="form-group">
                            <input class="inp-val form-control m-r-10" id="input_copy" type="text" value=""
                                readonly="readonly">
                            <button class="btn btn-primary notification" onclick="myFunction()">Copy text</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Mover Archivo -->
<div class="modal fade" id="modalMover" tabindex="-1">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Mover archivo a</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="index.php?page=empresas_contenedor&id=<?= (int)$empresa['id'] ?><?= $currentFolderId ? '&folder_id=' . (int)$currentFolderId : '' ?>">
        <div class="modal-body">
          <input type="hidden" name="action" value="mover">
          <input type="hidden" name="item_id" id="modalItemId" value="">
          
          <div class="list-group">
            <!-- Opción: Raíz -->
            <label class="list-group-item">
              <input type="radio" name="new_parent_id" value="" checked>
              <strong>Raíz</strong>
            </label>
            
            <!-- Carpetas disponibles -->
            <?php
            $todasLasCarpetas = $companyFilesModel->obtenerCarpetasRaizPorEmpresa((int)$empresa['id']);
            foreach ($todasLasCarpetas as $carpeta):
            ?>
              <label class="list-group-item">
                <input type="radio" name="new_parent_id" value="<?= (int)$carpeta['id'] ?>">
                <i class="fa fa-folder-o fa f-18"></i> <?= htmlspecialchars($carpeta['nombre'], ENT_QUOTES, 'UTF-8') ?>
              </label>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-primary btn-sm">Mover aquí</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalPreviewImagen" tabindex="-1" aria-labelledby="modalPreviewImagenLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalPreviewImagenLabel">Vista previa de imagen</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body text-center">
        <img id="previewImagen" src="" alt="Imagen" class="img-fluid rounded border">
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var modalPreview = document.getElementById('modalPreviewImagen');
  if (!modalPreview) return;

  modalPreview.addEventListener('show.bs.modal', function (event) {
    var button = event.relatedTarget;
    if (!button) return;

    var imageUrl = button.getAttribute('data-file-url');
    var img = modalPreview.querySelector('#previewImagen');

    if (img && imageUrl) {
      img.src = imageUrl;
    }
  });

  modalPreview.addEventListener('hidden.bs.modal', function () {
    var img = modalPreview.querySelector('#previewImagen');
    if (img) {
      img.src = ''; // limpiar para evitar imágenes viejas en cache visual
    }
  });
});
</script>

<script src="<?= BASE_URL ?>assets/js/icons/feather-icon/feather-icon-clipart.js"></script>
<script src="<?= BASE_URL ?>assets/js/typeahead/handlebars.js"></script>
<script src="<?= BASE_URL ?>assets/js/typeahead/typeahead.bundle.js"></script>
<script src="<?= BASE_URL ?>assets/js/typeahead/typeahead.custom.js"></script>
<script src="<?= BASE_URL ?>assets/js/typeahead-search/handlebars.js"></script>
<script src="<?= BASE_URL ?>assets/js/typeahead-search/typeahead-custom.js"></script>
<script src="<?= BASE_URL ?>assets/js/tooltip-init.js"></script>