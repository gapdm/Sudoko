<?php

class Sudoku {  
   
   private $_matrix;  
  
   public function __construct(array $matrix = null) {  
       if (!isset($matrix)) {  
           $this->_matrix = $this->_getEmptyMatrix();  
       } else {  
           $this->_matrix = $matrix;  
       }  
   }  
  
   public function generate() {  
       $this->_matrix = $this->_solve($this->_getEmptyMatrix());  
       $cells = array_rand(range(0, 80), 30);  
       $i = 0;  
       foreach ($this->_matrix as &$row) {  
           foreach ($row as &$cell) {  
               if (!in_array($i++, $cells)) {  
                   $cell = null;  
               }  
           }  
       }  
       return $this->_matrix;  
   }  
  
   public function solve() {  
       $this->_matrix = $this->_solve($this->_matrix);  
       return $this->_matrix;  
   }  
  
   public function getHtml() {  
      $html = '<table border="1">';
      for ($row = 0; $row < 9; $row++) {  
          $html .= '<tr>';
          for ($column = 0; $column < 9; $column++) {
              $cellValue = isset($this->_matrix[$row][$column]) ? $this->_matrix[$row][$column] : '';
              $html .= '<td>' . '<input type="number" value="' . $cellValue . '" class="cosito" min="1" max="9">' . '</td>'; 
          }  
          $html .= '</tr>'; 
      }  
      $html .= '</table>';
      return $html;
   }
  
   private function _getEmptyMatrix() {  
       return array_fill(0, 9, array_fill(0, 9, 0));  
   }  
  
   private function _solve($matrix) {  
       while(true) {  
           $options = array();  
           foreach ($matrix as $rowIndex => $row) {  
               foreach ($row as $columnIndex => $cell) {  
                   if (!empty($cell)) {  
                       continue;  
                   }  
                   $permissible = $this->_getPermissible($matrix, $rowIndex, $columnIndex);  
                   if (count($permissible) == 0) {  
                       return false;  
                   }  
                   $options[] = array(  
                       'rowIndex' => $rowIndex,  
                       'columnIndex' => $columnIndex,  
                       'permissible' => $permissible  
                   );  
               }  
           }  
           if (count($options) == 0) {  
               return $matrix;  
           }  
  
           usort($options, array($this, '_sortOptions'));  
  
           if (count($options[0]['permissible']) == 1) {  
               $matrix[$options[0]['rowIndex']][$options[0]['columnIndex']] = current($options[0]['permissible']);  
               continue;  
           }  
  
           foreach ($options[0]['permissible'] as $value) {  
               $tmp = $matrix;  
               $tmp[$options[0]['rowIndex']][$options[0]['columnIndex']] = $value;  
               if ($result = $this->_solve($tmp)) {  
                   return $result;  
               }  
           }  
  
           return false;  
       }  
   } 
   
   public function solveAndSave($nombre) {

      $host = "localhost";
      $usuario = "root";
      $contrasena = "";
      $base_de_datos = "sudoku";

      $conexion = mysqli_connect($host, $usuario, $contrasena, $base_de_datos);

      if (!$conexion) {
         die("Error al conectar con la base de datos: " . mysqli_connect_error());
      }

      $sudoku_resuelto = $this->solve();
      
      $sudoku_json = json_encode($sudoku_resuelto);

      $fechahora = date('Y-m-d H:i:s');

      $sql = "INSERT INTO sudoko (nombre, sudoku, fechahora) VALUES ('$nombre', '$sudoku_json', '$fechahora')";
      $resultado = mysqli_query($conexion, $sql);

      if ($resultado) {
          return true;
      } else {
          return false;
      }
   }
  
   private function _getPermissible($matrix, $rowIndex, $columnIndex) {  
       $valid = range(1, 9);  
       $invalid = $matrix[$rowIndex];  
       for ($i = 0; $i < 9; $i++) {  
           $invalid[] = $matrix[$i][$columnIndex];  
       }  
       $box_row = $rowIndex % 3 == 0 ? $rowIndex : $rowIndex - $rowIndex % 3;  
       $box_col = $columnIndex % 3 == 0 ? $columnIndex : $columnIndex - $columnIndex % 3;  
       $invalid = array_unique(array_merge(  
           $invalid,  
           array_slice($matrix[$box_row], $box_col, 3),  
           array_slice($matrix[$box_row + 1], $box_col, 3),  
           array_slice($matrix[$box_row + 2], $box_col, 3)  
       ));  
       $valid = array_diff($valid, $invalid);  
       shuffle($valid);  
       return $valid;  
   }  
  
   private function _sortOptions($a, $b) {  
       $a = count($a['permissible']);  
       $b = count($b['permissible']);  
       if ($a == $b) {  
           return 0;  
       }  
       return ($a < $b) ? -1 : 1;  
   }  
  
}  
   $sudoko = new Sudoku();
   $sudoko->generate();

   if (isset($_POST['accion']) && $_POST['accion'] == 'resolver') {
    $nombre = $_POST['nombre'];
    if ($sudoko->solveAndSave($nombre)) {
        echo "Sudoku resuelto y guardado correctamente.";
    } else {
        echo "Error al resolver y guardar el sudoku.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Document</title>
   <link rel="stylesheet" href="style.css">
</head>
<body>
   <h1>Sudoko</h1>
   <label for="nombre">Nombre:</label>
   <input type="text" name="nombre" id="nombre">
   <br><br>
   <?php echo $sudoko->getHtml(); ?>
   <script>

   function generar(){
      location.reload();
   }

   function resolver(){
      document.body.insertAdjacentHTML('beforeend','<?php $sudoko->solve(); ?>')
      document.body.insertAdjacentHTML('beforeend','<?php echo $sudoko->getHtml(); ?>')
      document.getElementById("solve").disabled = true;
      document.getElementsByClassName("cosito").disabled = true;

      var nombre = document.getElementById("txt_nombre").value;
      var xhr = new XMLHttpRequest();
      xhr.onreadystatechange = function() {
         if (xhr.readyState == 4 && xhr.status == 200) {
               alert(xhr.responseText);
               document.getElementById("solve").disabled = true;
               document.getElementsByClassName("cosito").disabled = true;
         }
      };
      xhr.open("POST", "index.php", true);
      xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
      xhr.send("accion=resolver&nombre=" + nombre);

   }

   </script><br><br>
   <button type="button" onclick='generar();' class="gen" id="gen">Generar</button>
   <button type="button" onclick='resolver();' class="solve" id="solve">Resolver</button>
   <a href="coso.php"><button>Soluciones</button></a>
   <br><br>
</body>
</html>