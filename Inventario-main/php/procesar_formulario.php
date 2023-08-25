<?php
require('fpdf/fpdf.php');

// Datos de conexión a la base de datos
$servername = "nombre_servidor";
$username = "nombre_usuario";
$password = "contraseña";
$dbname = "nombre_base_de_datos";

// Verificar si se recibieron datos del formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Obtener los datos del formulario
    $numeroFactura = $_POST['numero_factura'];
    $fecha = $_POST['fecha'];
    $cliente = $_POST['cliente'];
    $nombreProducto = $_POST['nombre_producto'];
    $precio = $_POST['precio'];
    $cantidad = $_POST['cantidad'];

    // Validar y limpiar los datos recibidos (puedes agregar más validaciones según tus necesidades)

    // Crear la conexión a la base de datos
    //$conn = new mysqli($servername, $username, $password, $dbname);
    $conn = new mysqli('localhost', 'root', '', 'facturacion_db');

    // Verificar la conexión
    if ($conn->connect_error) {
        die("Error en la conexión a la base de datos: " . $conn->connect_error);
    }

    // Insertar los datos en la tabla de facturas
    $sqlInsertFactura = "INSERT INTO facturas (numero_factura, fecha, cliente, monto_total) VALUES ('$numeroFactura', '$fecha', '$cliente', 0)";
    if ($conn->query($sqlInsertFactura) === TRUE) {
        $facturaId = $conn->insert_id; // Obtener el ID de la última factura insertada
    } else {
        die("Error al insertar la factura: " . $conn->error);
    }

    // Insertar los datos en la tabla de productos
    $sqlInsertProducto = "INSERT INTO productos (nombre, precio) VALUES ('$nombreProducto', '$precio')";
    if ($conn->query($sqlInsertProducto) === TRUE) {
        $productoId = $conn->insert_id; // Obtener el ID del último producto insertado
    } else {
        die("Error al insertar el producto: " . $conn->error);
    }

    // Calcular el subtotal y el monto total de la factura
    $subtotal = $precio * $cantidad;
    $montoTotal = $subtotal;

    // Actualizar el monto total de la factura en la tabla de facturas
    $sqlUpdateFactura = "UPDATE facturas SET monto_total = '$montoTotal' WHERE id = '$facturaId'";
    if ($conn->query($sqlUpdateFactura) !== TRUE) {
        die("Error al actualizar el monto total de la factura: " . $conn->error);
    }

    // Insertar los datos en la tabla de items_factura
    $sqlInsertItemFactura = "INSERT INTO items_factura (factura_id, producto_id, cantidad, subtotal) VALUES ('$facturaId', '$productoId', '$cantidad', '$subtotal')";
    if ($conn->query($sqlInsertItemFactura) !== TRUE) {
        die("Error al insertar el item de la factura: " . $conn->error);
    }

    // Cerrar la conexión a la base de datos
    $conn->close();

    // Generar el reporte en PDF
    generarReportePDF($numeroFactura, $fecha, $cliente, $nombreProducto, $precio, $cantidad, $subtotal, $montoTotal);

    // Redirigir al usuario a una página de confirmación o mostrar un mensaje de éxito
    header("Location: confirmacion.php");
    exit;
} else {
    // Si no se recibieron datos por POST, redirigir al formulario para evitar que se acceda directamente a este archivo
    header("Location: formulario_facturacion.php");
    exit;
}

// Función para generar el reporte en PDF
function generarReportePDF($numeroFactura, $fecha, $cliente, $nombreProducto, $precio, $cantidad, $subtotal, $montoTotal)
{
    // Crear el objeto PDF
    $pdf = new FPDF();
    $pdf->AddPage();

    // Configurar fuentes y tamaño de texto
    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, 'Reporte de Factura', 0, 1, 'C');

    // Agregar los datos de la factura al reporte
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, 'Número de Factura:', 0);
    $pdf->Cell(0, 10, $numeroFactura, 0, 1);
    $pdf->Cell(40, 10, 'Fecha:', 0);
    $pdf->Cell(0, 10, $fecha, 0, 1);
    $pdf->Cell(40, 10, 'Cliente:', 0);
    $pdf->Cell(0, 10, $cliente, 0, 1);

    // Agregar los detalles del producto al reporte
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Detalles del Producto', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, 'Nombre del Producto:', 0);
    $pdf->Cell(0, 10, $nombreProducto, 0, 1);
    $pdf->Cell(40, 10, 'Precio:', 0);
    $pdf->Cell(0, 10, '$' . number_format($precio, 2), 0, 1);
    $pdf->Cell(40, 10, 'Cantidad:', 0);
    $pdf->Cell(0, 10, $cantidad, 0, 1);
    $pdf->Cell(40, 10, 'Subtotal:', 0);
    $pdf->Cell(0, 10, '$' . number_format($subtotal, 2), 0, 1);

    // Agregar el monto total al reporte
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(0, 10, 'Monto Total:', 0, 1);
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(0, 10, '$' . number_format($montoTotal, 2), 0, 1);

    // Generar el archivo PDF
    $filename = "reporte_factura_" . date('Y-m-d_H-i-s') . ".pdf";
    $pdf->Output('F', $filename);
}
?>
