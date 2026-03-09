<?php

namespace App\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Supplier;
use App\Models\Sector;

class ImportController
{
    public function products()
    {
        $productModel = new Product();
        $categoryModel = new Category();
        $brandModel = new Brand();
        $supplierModel = new Supplier();
        $sectorModel = new Sector();
        
        $categories = $categoryModel->getAll();
        $brands = $brandModel->getAll();
        $suppliers = $supplierModel->getAll();
        $sectors = $sectorModel->getAll();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
                $error = "Erro ao fazer upload do arquivo.";
            } else {
                $file = $_FILES['file'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (!in_array($ext, ['xls', 'xlsx', 'csv'])) {
                    $error = "Formato de arquivo inválido. Use .xls, .xlsx ou .csv";
                } else {
                    // Verificar se é Excel e se temos suporte
                    if (in_array($ext, ['xls', 'xlsx']) && !class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
                        $error = "Para importar arquivos Excel (.xls/.xlsx), é necessário instalar a biblioteca PhpSpreadsheet. Por enquanto, use arquivos CSV ou converta seu Excel para CSV.";
                    } else {
                        $result = $this->processImport($file, $categories, $brands, $suppliers, $sectors);
                        
                        if ($result['success']) {
                            // Salvar mensagens de erro na sessão para exibir depois
                            if (!empty($result['errorMessages'])) {
                                $_SESSION['import_errors'] = $result['errorMessages'];
                            }
                            header('Location: ' . BASE_URL . '?route=import/products&success=imported&imported=' . $result['imported'] . '&errors=' . $result['errors']);
                            exit;
                        } else {
                            $error = $result['message'];
                        }
                    }
                }
            }
        }
        
        require 'views/import/products.php';
    }
    
    public function downloadTemplate()
    {
        // Criar planilha modelo
        $this->generateTemplate();
        exit;
    }
    
    private function processImport($file, $categories, $brands, $suppliers, $sectors)
    {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $data = [];
        
        if ($ext === 'csv') {
            $data = $this->readCSV($file['tmp_name']);
        } else {
            // Para Excel, usar biblioteca ou converter para CSV
            $data = $this->readExcel($file['tmp_name'], $ext);
        }
        
        if (empty($data)) {
            return ['success' => false, 'message' => 'Arquivo vazio ou formato inválido.'];
        }
        
        // Validar cabeçalhos
        $headers = array_shift($data);
        $expectedHeaders = ['nome', 'codigo', 'categoria', 'marca', 'preco', 'custo', 'estoque', 'unidade', 'localizacao', 'ean', 'observacoes', 'fornecedor', 'consignado'];
        $headerMap = $this->mapHeaders($headers, $expectedHeaders);
        
        if (empty($headerMap) || !isset($headerMap['nome']) || !isset($headerMap['categoria']) || !isset($headerMap['preco'])) {
            return ['success' => false, 'message' => 'Cabeçalhos inválidos. Os campos obrigatórios (nome, categoria, preco) não foram encontrados. Baixe o modelo e use-o como referência.'];
        }
        
        $productModel = new Product();
        $imported = 0;
        $errors = 0;
        $errorMessages = [];
        
        foreach ($data as $rowNum => $row) {
            // Normalizar array (pode ter índices não sequenciais)
            $row = array_values($row);
            
            // Verificar se linha está vazia
            if (empty(array_filter($row, function($v) { return $v !== null && trim($v) !== ''; }))) {
                continue; // Linha vazia
            }
            
            $productData = $this->mapRowToProduct($row, $headerMap, $categories, $brands, $suppliers, $sectors);
            
            if ($productData['valid']) {
                try {
                    if ($productModel->create($productData['data'])) {
                        $imported++;
                    } else {
                        $errors++;
                        $errorMessages[] = "Linha " . ($rowNum + 2) . ": Erro ao inserir produto no banco de dados.";
                    }
                } catch (Exception $e) {
                    $errors++;
                    $errorMessages[] = "Linha " . ($rowNum + 2) . ": " . $e->getMessage();
                }
            } else {
                $errors++;
                $errorMessages[] = "Linha " . ($rowNum + 2) . ": " . $productData['error'];
            }
        }
        
        // Limitar quantidade de mensagens de erro para não sobrecarregar a sessão
        $maxErrors = 50;
        if (count($errorMessages) > $maxErrors) {
            $errorMessages = array_slice($errorMessages, 0, $maxErrors);
            $errorMessages[] = "... e mais " . ($errors - $maxErrors) . " erro(s) não exibidos.";
        }
        
        // Limitar quantidade de mensagens de erro para não sobrecarregar a sessão
        $maxErrors = 100;
        if (count($errorMessages) > $maxErrors) {
            $errorMessages = array_slice($errorMessages, 0, $maxErrors);
            $errorMessages[] = "... e mais " . ($errors - $maxErrors) . " erro(s) não exibidos. Corrija os erros acima e tente novamente.";
        }
        
        return [
            'success' => true,
            'imported' => $imported,
            'errors' => $errors,
            'errorMessages' => $errorMessages
        ];
    }
    
    private function readCSV($filePath)
    {
        $data = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            // Tentar detectar delimitador
            $firstLine = fgets($handle);
            rewind($handle);
            $delimiter = (substr_count($firstLine, ';') > substr_count($firstLine, ',')) ? ';' : ',';
            
            while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
                if (!empty(array_filter($row))) { // Ignorar linhas completamente vazias
                    $data[] = $row;
                }
            }
            fclose($handle);
        }
        return $data;
    }
    
    private function readExcel($filePath, $ext)
    {
        // Tentar usar PhpSpreadsheet se disponível
        if (class_exists('PhpOffice\PhpSpreadsheet\IOFactory')) {
            try {
                $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader($ext === 'xlsx' ? 'Xlsx' : 'Xls');
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $data = [];
                
                foreach ($worksheet->getRowIterator() as $row) {
                    $rowData = [];
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    
                    foreach ($cellIterator as $cell) {
                        $value = $cell->getValue();
                        // Converter objetos de data para string
                        if ($value instanceof \PhpOffice\PhpSpreadsheet\Shared\Date) {
                            $value = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
                        }
                        $rowData[] = $value;
                    }
                    
                    if (!empty(array_filter($rowData, function($v) { return $v !== null && $v !== ''; }))) {
                        $data[] = $rowData;
                    }
                }
                return $data;
            } catch (Exception $e) {
                error_log("Erro ao ler Excel: " . $e->getMessage());
                return [];
            }
        } else {
            // Se não tiver PhpSpreadsheet, retornar vazio (erro já foi tratado antes)
            return [];
        }
    }
    
    private function mapHeaders($headers, $expected)
    {
        $map = [];
        // Normalizar headers (remover BOM, espaços, converter para minúsculas)
        $headerLower = array_map(function($h) {
            return trim(strtolower(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $h)));
        }, $headers);
        
        foreach ($expected as $expectedHeader) {
            $found = false;
            foreach ($headerLower as $idx => $header) {
                // Comparação exata ou parcial
                if ($header === $expectedHeader || 
                    strpos($header, $expectedHeader) !== false || 
                    strpos($expectedHeader, $header) !== false) {
                    $map[$expectedHeader] = $idx;
                    $found = true;
                    break;
                }
            }
        }
        
        // Verificar se campos obrigatórios foram encontrados
        $required = ['nome', 'categoria', 'preco'];
        foreach ($required as $req) {
            if (!isset($map[$req])) {
                return [];
            }
        }
        
        return $map;
    }
    
    private function mapRowToProduct($row, $headerMap, $categories, $brands, $suppliers, $sectors)
    {
        $getValue = function($key) use ($row, $headerMap) {
            if (!isset($headerMap[$key])) {
                return '';
            }
            $idx = $headerMap[$key];
            return isset($row[$idx]) ? trim((string)$row[$idx]) : '';
        };
        
        $name = $getValue('nome');
        if (empty($name)) {
            return ['valid' => false, 'error' => 'Nome é obrigatório'];
        }
        
        // Mapear categoria
        $categoryId = null;
        $categoryName = $getValue('categoria');
        if (!empty($categoryName)) {
            foreach ($categories as $cat) {
                if (strcasecmp($cat['name'], $categoryName) === 0) {
                    $categoryId = $cat['id'];
                    break;
                }
            }
            if (!$categoryId) {
                return ['valid' => false, 'error' => "Categoria '{$categoryName}' não encontrada"];
            }
        } else {
            return ['valid' => false, 'error' => 'Categoria é obrigatória'];
        }
        
        // Mapear marca
        $brandId = null;
        $brandName = $getValue('marca');
        if (!empty($brandName)) {
            foreach ($brands as $brand) {
                if (strcasecmp($brand['name'], $brandName) === 0) {
                    $brandId = $brand['id'];
                    break;
                }
            }
        }
        
        // Mapear fornecedor
        $supplierId = null;
        $supplierName = $getValue('fornecedor');
        if (!empty($supplierName)) {
            foreach ($suppliers as $supp) {
                if (strcasecmp($supp['name'], $supplierName) === 0) {
                    $supplierId = $supp['id'];
                    break;
                }
            }
        }
        
        $price = str_replace(',', '.', $getValue('preco'));
        if (empty($price) || !is_numeric($price)) {
            return ['valid' => false, 'error' => 'Preço inválido'];
        }
        
        $costPrice = str_replace(',', '.', $getValue('custo'));
        if (empty($costPrice)) $costPrice = 0;
        
        $stock = $getValue('estoque');
        if (empty($stock)) $stock = 0;
        
        $sectorId = $_SESSION['sector_id'] ?? 1;
        
        return [
            'valid' => true,
            'data' => [
                'name' => $name,
                'code' => $getValue('codigo'),
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'price' => $price,
                'cost_price' => $costPrice,
                'stock' => $stock,
                'unit' => $getValue('unidade') ?: 'UN',
                'location' => $getValue('localizacao'),
                'ean' => $getValue('ean'),
                'observations' => $getValue('observacoes'),
                'image' => null,
                'is_gift_card' => 0,
                'is_consigned' => strtolower($getValue('consignado')) === 'sim' ? 1 : 0,
                'supplier_id' => $supplierId,
                'sector_id' => $sectorId
            ]
        ];
    }
    
    private function generateTemplate()
    {
        // Buscar dados para exemplos
        $categoryModel = new Category();
        $categories = $categoryModel->getAll();
        $categoryExample = !empty($categories) ? $categories[0]['name'] : 'Terços';
        
        $brandModel = new Brand();
        $brands = $brandModel->getAll();
        $brandExample = !empty($brands) ? $brands[0]['name'] : '';
        
        $supplierModel = new Supplier();
        $suppliers = $supplierModel->getAll();
        $supplierExample = !empty($suppliers) ? $suppliers[0]['name'] : '';
        
        // Criar CSV modelo (compatível com Excel)
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="modelo_importacao_produtos.csv"');
        
        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8 (Excel)
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        
        // Cabeçalhos
        fputcsv($output, [
            'nome',
            'codigo',
            'categoria',
            'marca',
            'preco',
            'custo',
            'estoque',
            'unidade',
            'localizacao',
            'ean',
            'observacoes',
            'fornecedor',
            'consignado'
        ], ';');
        
        // Linhas de exemplo
        fputcsv($output, [
            'Terço de Madeira',
            'TER-001',
            $categoryExample,
            $brandExample,
            '25.50',
            '15.00',
            '10',
            'UN',
            'Prateleira A1',
            '7891234567890',
            'Produto importado',
            $supplierExample,
            'Não'
        ], ';');
        
        fputcsv($output, [
            'Bíblia Sagrada',
            'BIB-001',
            $categoryExample,
            $brandExample,
            '89.90',
            '50.00',
            '5',
            'UN',
            'Prateleira B2',
            '',
            '',
            $supplierExample,
            'Sim'
        ], ';');
        
        fclose($output);
    }
}
