<?php
// app/views/usuarios/index.php
require APP_PATH . '/views/layout/app_start.php';
?>
<div class="row">
  <div class="col-xl-5 col-lg-6">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0"><?= $editar ? 'Editar usuario' : 'Nuevo usuario' ?></h5>
      </div>
      <div class="card-body">
        <?php if ($editar && !empty($editar['foto'])): ?>
        <div class="text-center mb-3">
          <img class="rounded-circle" src="<?= BASE_URL . htmlspecialchars($editar['foto']) ?>" alt="Foto de perfil" style="width: 100px; height: 100px; object-fit: cover; border: 3px solid #eee;">
        </div>
        <?php endif; ?>
        <form method="post" action="index.php?page=usuarios">
          <input type="hidden" name="_action" value="<?= $editar ? 'editar' : 'crear' ?>">
          <?php if ($editar): ?><input type="hidden" name="id" value="<?= $editar['id'] ?>"><?php endif; ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Nombre</label>
              <input class="form-control" type="text" name="nombre" required value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Apellido</label>
              <input class="form-control" type="text" name="apellido" value="<?= htmlspecialchars($editar['apellido'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Cédula</label>
              <input class="form-control" type="text" name="cedula" value="<?= htmlspecialchars($editar['cedula'] ?? '') ?>">
            </div>
            <div class="col-md-8">
              <label class="form-label">Email</label>
              <input class="form-control" type="email" name="email" required value="<?= htmlspecialchars($editar['email'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Login</label>
              <input class="form-control" type="text" name="login" required value="<?= htmlspecialchars($editar['login'] ?? '') ?>">
            </div>
            <div class="col-md-8">
              <label class="form-label">Contraseña</label>
              <input class="form-control" type="password" name="password" placeholder="<?= $editar ? 'Dejar en blanco para no cambiar' : 'Contraseña' ?>" <?= $editar ? '' : 'required' ?>>
            </div>
            <div class="col-md-4">
              <label class="form-label">Cargo</label>
              <input class="form-control" type="text" name="cargo" value="<?= htmlspecialchars($editar['cargo'] ?? '') ?>">
            </div>
            <div class="col-md-8">
              <label class="form-label">Teléfono</label>
              <input class="form-control" type="text" name="telefono" value="<?= htmlspecialchars($editar['telefono'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Rol</label>
              <select class="form-select" name="rol">
                <option value="asistente" <?= ($editar['rol'] ?? '') === 'asistente' ? 'selected' : '' ?>>Asistente</option>
                <option value="admin" <?= ($editar['rol'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="superadmin" <?= ($editar['rol'] ?? '') === 'superadmin' ? 'selected' : '' ?>>Superadmin</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label">Estado</label>
              <select class="form-select" name="estado">
                <option value="activo" <?= ($editar['estado'] ?? '') === 'activo' ? 'selected' : '' ?>>Activo</option>
                <option value="inactivo" <?= ($editar['estado'] ?? '') === 'inactivo' ? 'selected' : '' ?>>Inactivo</option>
              </select>
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary"><i class="fa fa-save me-2"></i><?= $editar ? 'Guardar cambios' : 'Crear usuario' ?></button>
              <?php if ($editar): ?><a href="index.php?page=usuarios" class="btn btn-outline-secondary">Cancelar</a><?php endif; ?>
            </div>
          </div>
        </form>

        <?php if ($editar): ?>
        <div class="card mt-3">
          <div class="card-header">
            <h6 class="mb-0"><i class="fa fa-camera me-2"></i>Foto de perfil</h6>
          </div>
          <div class="card-body">
            <div class="text-center mb-3">
              <?php if (!empty($editar['foto'])): ?>
                <img src="<?= BASE_URL . htmlspecialchars($editar['foto']) ?>" alt="Foto actual" style="width: 120px; height: 120px; object-fit: cover; border-radius: 50%; border: 3px solid #eee;">
                <p class="small text-muted mt-2">Foto actual</p>
              <?php else: ?>
                <div style="width: 120px; height: 120px; border-radius: 50%; background: #f0f0f0; display: inline-flex; align-items: center; justify-content: center;">
                  <i class="fa fa-user fa-3x text-muted"></i>
                </div>
                <p class="small text-muted mt-2">Sin foto</p>
              <?php endif; ?>
            </div>
            <form method="post" action="index.php?page=usuarios_cambiar_foto" enctype="multipart/form-data">
              <input type="hidden" name="id" value="<?= (int)$editar['id'] ?>">
              <label class="form-label">Seleccionar imagen</label>
              <input class="form-control" type="file" name="foto" accept="image/*" required>
              <small class="form-text text-muted">Formatos: JPG, PNG, GIF. Tamaño máx: 2MB.</small>
              <button type="submit" class="btn btn-primary btn-sm mt-2"><i class="fa fa-upload me-1"></i>Subir foto</button>
            </form>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-xl-7 col-lg-6">
    <div class="card">
      <div class="card-header">
        <h5 class="mb-0">Usuarios de la clínica</h5>
      </div>
      <div class="card-body">
        <?php if (!empty($msg)): ?>
          <div class="alert alert-success py-2"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
        <?php if (!empty($_GET['m'])): ?>
          <div class="alert alert-success py-2"><?= htmlspecialchars($_GET['m']) ?></div>
        <?php endif; ?>
        <div class="table-responsive">
          <table class="table table-hover">
            <thead class="table-light">
              <tr><th>Foto</th><th>Nombre</th><th>Login</th><th>Email</th><th>Rol</th><th>Estado</th><th class="text-end">Acciones</th></tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $u): ?>
              <tr>
                <td>
                  <?php if (!empty($u['foto'])): ?>
                    <img src="<?= BASE_URL . htmlspecialchars($u['foto']) ?>" alt="Foto" style="width: 40px; height: 40px; object-fit: cover; border-radius: 50%;">
                  <?php else: ?>
                    <span class="text-muted"><i class="fa fa-user-circle fa-lg"></i></span>
                  <?php endif; ?>
                </td>
                <td><?= htmlspecialchars(($u['nombre'] ?? '') . ' ' . ($u['apellido'] ?? '')) ?></td>
                <td><?= htmlspecialchars($u['login'] ?? '') ?></td>
                <td><?= htmlspecialchars($u['email'] ?? '') ?></td>
                <td><span class="badge badge-light-primary"><?= htmlspecialchars($u['rol'] ?? '') ?></span></td>
                <td><span class="badge badge-light-<?= ($u['estado'] ?? '') === 'activo' ? 'success' : 'danger' ?>"><?= htmlspecialchars($u['estado'] ?? '') ?></span></td>
                <td class="text-end text-nowrap">
                  <a class="btn btn-outline-primary btn-sm" href="index.php?page=usuarios&edit=<?= $u['id'] ?>"><i class="fa fa-pencil"></i></a>
                  <form method="post" style="display:inline" onsubmit="return confirm('¿Eliminar?')">
                    <input type="hidden" name="_action" value="eliminar">
                    <input type="hidden" name="id" value="<?= $u['id'] ?>">
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa fa-trash"></i></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require APP_PATH . '/views/layout/app_end.php'; ?>