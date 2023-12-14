<?php

function imprimir_donaciones($donaciones)
{
    if ($donaciones) {
        while ($donacion = $donaciones->fetch()) {
            $fechaDonacion = date_create($donacion['fechaDonacion']);
            $fechaDonacionFormateada = date_format($fechaDonacion, 'd/m/Y');
            $proxDonacion = date_create($donacion['proximaDonacion']);
            $proxDonacionFormateada = date_format($proxDonacion, 'd/m/Y');

            echo "<tr>";
            echo "<td>" . $donacion['nombre'] . "</td>";
            echo "<td>" . $donacion['apellidos'] . "</td>";
            echo "<td>" . $donacion['fechaDonacion'] . "</td>";
            echo "<td>" . $donacion['proximaDonacion'] . "</td>";
            echo "</tr>";
        }
    }
}
