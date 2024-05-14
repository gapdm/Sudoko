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
       echo '';  
       for ($row = 0; $row < 9; $row++) {  
           echo '';  
           for ($column = 0; $column < 9; $column++) {  
               echo '';  
           }  
           echo '';  
       }  
       echo '<table border="1"><tbody><tr><td>' . $this->_matrix[$row][$column] . '</td></tr></tbody></table>';  
   }  
  
   private function _getEmptyMatrix() {  
       return array_fill(0, 9, array_fill(0, 9, 0));  
   }  
  
   private function _solve($matrix) {  
       while(true) {  
           $options = array();  
           foreach ($matrix as $rowIndex => $row) {  
               foreach ($row as $columnIndex => $cell) {  
                   if (!emptyempty($cell)) {  
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