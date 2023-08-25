<?php
// Archivo de reporte generado previamente
$filename = "reporte_facturas_" . date('Y-m-d_H-i-s') . ".csv";

// Descargar el archivo
if (file_exists($filename)) {
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    readfile($filename);
} else {
    echo "El reporte no está disponible actualmente.";
}
?>