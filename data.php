<?php
function countProjects() {
  return 33; 
}

function countDocuments() {
  return 198;
}

function countStored() {
  return 167;
}

function countReleased() {
  return 21;
}

function getRecentActivity() {
  return [
    ['type' => 'TAX DECLARATION', 'action' => 'RETRIEVE', 'doc_id' => 'HAG-101', 'date' => 'April 22, 2025'],
    ['type' => 'LOT TITLE', 'action' => 'MODIFIED', 'doc_id' => 'HAG-102', 'date' => 'April 22, 2025'],
    ['type' => 'DEED OF SALE', 'action' => 'UPDATE', 'doc_id' => 'HAG-103', 'date' => 'April 22, 2025'],
    ['type' => 'TAX DECLARATION', 'action' => 'RETRIEVE', 'doc_id' => 'CAL-101', 'date' => 'April 21, 2025'],
    ['type' => 'TAX DECLARATION', 'action' => 'MODIFIED', 'doc_id' => 'BUL-101', 'date' => 'April 21, 2025'],
  ];
}
