<?php
require $_SERVER['DOCUMENT_ROOT'] . '/sisti/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

class GeneradorPDF
{
    private $dompdf;
    private $html;

    public function __construct()
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $this->dompdf = new Dompdf($options);
        $this->html = '';
    }

    public function generar($titulo, $encabezados, $datos, $nombreArchivo)
    {
        // Mapa para clases CSS según campo
        $mapaClases = [
            'numero_ticket' => 'col-ticket',
            'dni' => 'col-dni',
            'nombre' => 'col-nombre',
            'apellido' => 'col-apellido',
            'area' => 'col-area',
            'descripcion' => 'col-descripcion',
            'estado_texto' => 'col-estado',
            'fecha_creacion' => 'col-fecha',
            'fecha_resuelto' => 'col-fecha',
        ];

        // Estilos CSS
        $this->html .= '<style>
            body { font-family: Arial, sans-serif; font-size: 11px; }
            h2 { text-align: center; margin-bottom: 10px; }
            table { width: 100%; border-collapse: collapse; margin-top: 10px; }
            th, td { border: 1px solid #666; padding: 4px; }
            th { background-color: #2563EB; color: white; text-align: center; }
            td.center { text-align: center; }
            .col-numero { width: 4%; }
            .col-ticket { width: 16%; }
            .col-dni { width: 10%; text-align: center; }
            .col-nombre { width: 17%; }
            .col-apellido { width: 17%; }
            .col-area { width: 14%; }
            .col-descripcion { width: 16%; }
            .col-estado { width: 8%; text-align: center; }
            .col-fecha { width: 8%; text-align: center; }
        </style>';

        $this->html .= "<h2>$titulo</h2>";
        $this->html .= '<table>';
        $this->html .= '<thead><tr>';
        $this->html .= '<th class="col-numero">N°</th>';

        foreach ($encabezados as $key) {
            $nombreMostrar = $key;
            if ($key === 'fecha_creacion') $nombreMostrar = 'Fecha Creación';
            if ($key === 'fecha_resuelto') $nombreMostrar = 'Fecha Resuelto';

            $claseTH = $mapaClases[$key] ?? '';
            $this->html .= "<th class=\"$claseTH\">" . ucfirst($nombreMostrar) . "</th>";
        }

        $this->html .= '</tr></thead><tbody>';

        foreach ($datos as $index => $fila) {
            $this->html .= '<tr>';
            $this->html .= '<td class="center col-numero">' . ($index + 1) . '</td>';

            foreach ($fila as $clave => $valor) {
                // Formatear fechas
                if (in_array($clave, ['fecha_creacion', 'fecha_resuelto']) && !empty($valor)) {
                    try {
                        $valor = (new DateTime($valor))->format('d/m/Y H:i:s');
                    } catch (Exception $e) {
                        // Mantener el valor original si hay error
                    }
                }

                // Definir clases para celdas
                $clase = in_array($clave, ['numero_ticket', 'dni', 'fecha_creacion', 'fecha_resuelto', 'estado_texto']) ? 'center' : '';
                $columnaClass = $mapaClases[$clave] ?? '';
                $clasesTD = trim($clase . ' ' . $columnaClass);

                $this->html .= '<td class="' . $clasesTD . '">' . htmlspecialchars($valor) . '</td>';
            }

            $this->html .= '</tr>';
        }

        $this->html .= '</tbody></table>';

        // Renderizar PDF
        $this->dompdf->loadHtml($this->html);
        $this->dompdf->setPaper('A4', 'landscape');
        $this->dompdf->render();
        $this->dompdf->stream($nombreArchivo . ".pdf", ['Attachment' => true]);
        exit;
    }
}
