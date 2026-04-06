<?php
// Genera JSONs automáticamente para las 4 carpetas
function generateAssetsJson($dir) {
    $files = [];
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    foreach ($iterator as $file) {
        if ($file->isFile()) {
            $files[] = [
                'name' => $file->getFilename(),
                'fullPath' => substr($file->getPathname(), strlen($_SERVER['DOCUMENT_ROOT'])),
                'size' => $file->getSize(),
                'folder' => str_replace($dir, '', dirname($file->getPathname()))
            ];
        }
    }
    usort($files, function($a, $b) { return strcmp($a['name'], $b['name']); });
    file_put_contents("$dir/files.json", json_encode(['files' => $files], JSON_PRETTY_PRINT));
}

function generateFoldersJson($dir) {
    $folders = [];
    $iterator = new DirectoryIterator($dir);
    
    foreach ($iterator as $folder) {
        if ($folder->isDir() && !$folder->isDot()) {
            $folderName = $folder->getFilename();
            $folderPath = "$dir/$folderName";
            $htmlFiles = [];
            
            if (is_dir($folderPath)) {
                $fileIterator = new DirectoryIterator($folderPath);
                foreach ($fileIterator as $file) {
                    if ($file->isFile() && preg_match('/\.(htm|html|php)$/i', $file->getFilename())) {
                        $htmlFiles[] = [
                            'name' => $file->getFilename(),
                            'size' => $file->getSize()
                        ];
                    }
                }
                usort($htmlFiles, function($a, $b) { return strcmp($a['name'], $b['name']); });
            }
            
            if (!empty($htmlFiles)) {
                $folders[] = [
                    'name' => $folderName,
                    'files' => $htmlFiles
                ];
            }
        }
    }
    usort($folders, function($a, $b) { return strcmp($a['name'], $b['name']); });
    file_put_contents("$dir/folders.json", json_encode(['folders' => $folders], JSON_PRETTY_PRINT));
}

// Generar JSONs al cargar la página (solo si no existen o están desactualizados)
$folders = ['assets', 'landing', 'blog', 'brochure'];
foreach ($folders as $folder) {
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
    
    if ($folder === 'assets') {
        if (!file_exists("$folder/files.json") || filemtime($folder) > filemtime("$folder/files.json")) {
            generateAssetsJson("./$folder/");
        }
    } else {
        if (!file_exists("$folder/folders.json") || filemtime($folder) > filemtime("$folder/folders.json")) {
            generateFoldersJson("./$folder/");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>(php) Gestor de Archivos por Tabs</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .file-card {
            border-left: 4px solid #007bff;
            transition: all 0.3s ease;
        }
        .file-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,123,255,0.2);
        }
        .accordion-button {
            font-weight: 600;
        }
        .file-icon {
            width: 24px;
            margin-right: 10px;
        }
        .loading {
            display: none;
            text-align: center;
            padding: 40px;
        }
        .tab-content {
            padding: 20px;
        }
        .folder-path {
            font-size: 0.85em;
            color: #6c757d;
        }
        .status-badge {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 0.75em;
        }
    </style>
</head>
<body>
    <div class="container-fluid py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10 col-xl-8">
                <!-- Header con estado -->
                <div class="text-center mb-5 position-relative">
                    <h1 class="display-4 text-white fw-bold mb-3">
                        <i class="fas fa-folder-tree me-3"></i>
                        Gestor de Archivos
                    </h1>
                    <p class="lead text-white-50 mb-2">JSONs actualizados automáticamente</p>
                    <span class="badge bg-success status-badge">
                        <i class="fas fa-check me-1"></i>Actualizado
                    </span>
                </div>

                <!-- Tabs (SIN CAMBIOS) -->
                <ul class="nav nav-tabs nav-fill mb-4 justify-content-center" id="fileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="assets-tab" data-bs-toggle="tab" data-bs-target="#assets" type="button" role="tab">
                            <i class="fas fa-gem me-2"></i>Assets
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="landing-tab" data-bs-toggle="tab" data-bs-target="#landing" type="button" role="tab">
                            <i class="fas fa-rocket me-2"></i>Landing
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="blog-tab" data-bs-toggle="tab" data-bs-target="#blog" type="button" role="tab">
                            <i class="fas fa-blog me-2"></i>Blog
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="brochure-tab" data-bs-toggle="tab" data-bs-target="#brochure" type="button" role="tab">
                            <i class="fas fa-file-alt me-2"></i>Brochure
                        </button>
                    </li>
                </ul>

                <!-- Tab Content (SIN CAMBIOS) -->
                <div class="tab-content bg-white rounded-4 shadow-lg overflow-hidden" id="fileTabContent">
                    <!-- Assets Tab -->
                    <div class="tab-pane fade show active" id="assets" role="tabpanel">
                        <div class="loading text-primary">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p class="mb-0">Cargando assets...</p>
                        </div>
                        <div id="assets-content"></div>
                    </div>

                    <!-- Landing Tab -->
                    <div class="tab-pane fade" id="landing" role="tabpanel">
                        <div class="loading text-success">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p class="mb-0">Cargando landing pages...</p>
                        </div>
                        <div id="landing-content"></div>
                    </div>

                    <!-- Blog Tab -->
                    <div class="tab-pane fade" id="blog" role="tabpanel">
                        <div class="loading text-info">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p class="mb-0">Cargando blog...</p>
                        </div>
                        <div id="blog-content"></div>
                    </div>

                    <!-- Brochure Tab -->
                    <div class="tab-pane fade" id="brochure" role="tabpanel">
                        <div class="loading text-warning">
                            <i class="fas fa-spinner fa-spin fa-2x mb-3"></i>
                            <p class="mb-0">Cargando brochures...</p>
                        </div>
                        <div id="brochure-content"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts (USA LA VERSIÓN ORIGINAL que funcionaba perfectamente) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
    $(document).ready(function() {
        // Configuración de carpetas base
        const baseFolders = {
            assets: 'assets/',
            landing: 'landing/',
            blog: 'blog/',
            brochure: 'brochure/'
        };

        // Iconos por tipo de archivo (TODOS incluidos)
        const fileIcons = {
            pdf: 'fas fa-file-pdf text-danger',
            doc: 'fas fa-file-word text-primary',
            docx: 'fas fa-file-word text-primary',
            xls: 'fas fa-file-excel text-success',
            xlsx: 'fas fa-file-excel text-success',
            xlsm: 'fas fa-file-excel text-success',
            csv: 'fas fa-file-csv text-info',
            txt: 'fas fa-file-alt text-secondary',
            json: 'fas fa-file-code text-warning',
            png: 'fas fa-file-image text-warning',
            jpg: 'fas fa-file-image text-warning',
            jpeg: 'fas fa-file-image text-warning',
            gif: 'fas fa-file-image text-info',
            htm: 'fas fa-file-code text-success',
            html: 'fas fa-file-code text-success',
            php: 'fas fa-file-code text-purple',
            zip: 'fas fa-file-archive text-secondary',
            default: 'fas fa-file text-muted'
        };

        function getFileIcon(ext) {
            ext = ext.toLowerCase();
            return fileIcons[ext] || fileIcons.default;
        }

        function formatFolderName(name) {
            return name.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        }

        // FUNCIÓN ORIGINAL para Assets (agrupados por tipo)
        function loadAssets() {
            $.getJSON(baseFolders.assets + 'files.json', function(data) {
                const $content = $('#assets-content');
                $content.empty();

                if (data.files && data.files.length > 0) {
                    const filesByType = {};
                    data.files.forEach(file => {
                        const ext = file.name.split('.').pop().toLowerCase();
                        if (!filesByType[ext]) filesByType[ext] = [];
                        filesByType[ext].push(file);
                    });

                    Object.keys(filesByType).sort().forEach(ext => {
                        filesByType[ext].sort((a, b) => a.name.localeCompare(b.name));

                        $content.append(`
                            <div class="accordion mb-3 file-card" id="assets-${ext}">
                                <div class="accordion-item">
                                    <h3 class="accordion-header">
                                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#assets-${ext}-collapse">
                                            <i class="${getFileIcon(ext)} file-icon"></i>
                                            <strong>${ext.toUpperCase()}</strong> (${filesByType[ext].length} archivos)
                                        </button>
                                    </h3>
                                    <div id="assets-${ext}-collapse" class="accordion-collapse collapse" data-bs-parent="#assets-${ext}">
                                        <div class="accordion-body">
                                            ${filesByType[ext].map(file => `
                                                <div class="d-flex align-items-center p-2 border-bottom file-item">
                                                    <i class="${getFileIcon(file.name)} file-icon me-3"></i>
                                                    <a href="${file.fullPath}" target="_blank" class="text-decoration-none flex-grow-1" title="${file.fullPath}">
                                                        ${file.name}
                                                    </a>
                                                    <div class="ms-2">
                                                        <span class="badge bg-light text-dark">${(file.size/1024).toFixed(1)} KB</span>
                                                        ${file.folder && file.folder !== '' ? `<br><small class="folder-path"><i class="fas fa-folder me-1"></i>${file.folder}</small>` : ''}
                                                    </div>
                                                </div>
                                            `).join('')}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `);
                    });
                } else {
                    $content.html('<div class="text-center py-5"><i class="fas fa-folder-open fa-3x text-muted mb-3"></i><h5 class="text-muted">No se encontraron archivos en assets</h5></div>');
                }
                $('#assets .loading').hide();
            }).fail(function() {
                $('#assets .loading').hide();
                $('#assets-content').html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No se pudo cargar assets</div>');
            });
        }

        // FUNCIÓN ORIGINAL para las otras carpetas (SIN CAMBIOS)
        function loadFolderStructure(folderKey, contentId) {
            $.getJSON(baseFolders[folderKey] + 'folders.json', function(data) {
                const $content = $(`#${contentId}`);
                $content.empty();

                if (data.folders && data.folders.length > 0) {
                    data.folders.forEach(folder => {
                        const htmlFiles = folder.files.filter(file => 
                            file.name.match(/\.(htm|html|php)$/i)
                        ).sort((a, b) => a.name.localeCompare(b.name));

                        if (htmlFiles.length > 0) {
                            $content.append(`
                                <div class="accordion mb-3 file-card" id="${folderKey}-${folder.name}">
                                    <div class="accordion-item">
                                        <h3 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#${folderKey}-${folder.name}-collapse">
                                                <i class="fas fa-folder me-2 text-primary"></i>
                                                <strong>${formatFolderName(folder.name)}</strong> (${htmlFiles.length} páginas)
                                            </button>
                                        </h3>
                                        <div id="${folderKey}-${folder.name}-collapse" class="accordion-collapse collapse" data-bs-parent="#${folderKey}-${folder.name}">
                                            <div class="accordion-body">
                                                ${htmlFiles.map(file => `
                                                    <div class="d-flex align-items-center p-2 border-bottom file-item">
                                                        <i class="${getFileIcon(file.name)} file-icon me-3"></i>
                                                        <a href="${baseFolders[folderKey]}${folder.name}/${file.name}" target="_blank" class="text-decoration-none flex-grow-1">
                                                            ${file.name}
                                                        </a>
                                                        <span class="badge bg-light text-dark">${(file.size/1024).toFixed(1)} KB</span>
                                                    </div>
                                                `).join('')}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `);
                        }
                    });
                } else {
                    $content.html('<div class="text-center py-5"><i class="fas fa-folder-open fa-3x text-muted mb-3"></i><h5 class="text-muted">No se encontraron carpetas</h5></div>');
                }
                $(`#${folderKey} .loading`).hide();
            }).fail(function() {
                $(`#${folderKey} .loading`).hide();
                $(`#${contentId}`).html('<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>No se pudo cargar el listado</div>');
            });
        }

        // Event listeners (SIN CAMBIOS)
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
            const targetId = $(e.target).data('bs-target');
            const tabName = targetId.replace('#', '');

            $(`${targetId} .loading`).show();
            $(`${targetId}-content`).empty();

            switch(tabName) {
                case 'assets':
                    loadAssets();
                    break;
                case 'landing':
                    loadFolderStructure('landing', 'landing-content');
                    break;
                case 'blog':
                    loadFolderStructure('blog', 'blog-content');
                    break;
                case 'brochure':
                    loadFolderStructure('brochure', 'brochure-content');
                    break;
            }
        });

        // Cargar Assets por defecto
        loadAssets();
    });
    </script>
</body>
</html>
