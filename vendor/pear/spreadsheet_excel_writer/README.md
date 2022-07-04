[![Build Status](https://travis-ci.org/pear/Spreadsheet_Excel_Writer.svg?branch=master)](https://travis-ci.org/pear/Spreadsheet_Excel_Writer)
[![Latest Stable Version](https://poser.pugx.org/pear/spreadsheet_excel_writer/v/stable)](https://packagist.org/packages/pear/spreadsheet_excel_writer)
[![Coverage Status](https://coveralls.io/repos/github/pear/Spreadsheet_Excel_Writer/badge.svg?branch=master)](https://coveralls.io/github/pear/Spreadsheet_Excel_Writer?branch=master)

# Spreadsheet_Excel_Writer

This package is [Spreadsheet_Excel_Writer](http://pear.php.net/package/Spreadsheet_Excel_Writer) and has been migrated from [svn.php.net](https://svn.php.net/repository/pear/packages/Spreadsheet_Excel_Writer).

Please report all new issues [via the PEAR bug tracker](http://pear.php.net/bugs/search.php?cmd=display&package_name[]=Spreadsheet_Excel_Writer&order_by=ts1&direction=DESC&status=Open).

If this package is marked as unmaintained and you have fixes, please submit your pull requests and start discussion on the pear-qa mailing list.


# Installation

## Pear

To test, run

    $ phpunit

To build, simply

    $ pear package

To install from scratch

    $ pear install package.xml

To upgrade

    $ pear upgrade -f package.xml

## Composer

This package comes with support for Composer.

To install from Composer

    $ composer require pear/spreadsheet_excel_writer

To install the latest development version

    $ composer require pear/spreadsheet_excel_writer:dev-master

# Features

- writing Excel (.XLS) spreadsheets
- support: strings (with formatting for text and cells), formulas, images (BMP) 

# Limitations
Library support only 2 types of format for writing XLS, also known as Binary Interchange File Format ([BIFF](https://www.openoffice.org/sc/excelfileformat.pdf)): 
- BIFF5 (Excel 5.0 - Excel 95)
- BIFF8 (Excel 98 - Excel 2003)

**Some important limitations:**  

| Limit | BIFF5 | BIFF8 |
| --- | --- | --- |
| Maximum number of rows | 16384 | 65535 |
| Maximum number of columns | 255 | 255 |
| Maximum data size of a record | 2080 bytes | 8224 bytes |
| Unicode support | CodePage based character encoding | UTF-16LE |

Explanation of formats and specifications you can find [here](https://www.loc.gov/preservation/digital/formats/fdd/fdd000510.shtml) (section "Useful references")

Correct output only guaranteed with `mbstring.func_overload = 0` otherwise, you should use workround `mb_internal_encoding('latin1');` 

# Usage

## Basic usage
```php
use Spreadsheet_Excel_Writer;


$filePath = __DIR__ . '/output/out.xls';
$xls = new Spreadsheet_Excel_Writer($filePath);

// 8 = BIFF8
$xls->setVersion(8);

$sheet = $xls->addWorksheet('info');

// only available with BIFF8
$sheet->setInputEncoding('UTF-8');

$headers = [
    'id',
    'name',
    'email',
    'code',
    'address'
];

$row = $col = 0;
foreach ($headers as $header) {
    $sheet->write($row, $col, $header);
    $col++;
}

for ($id = 1; $id < 100; $id++) {
    $data = [
        'id' => $id,
        'name' => 'Name Surname',
        'email' => 'mail@gmail.com',
        'password' => 'cfcd208495d565ef66e7dff9f98764da',
        'address' => '00000 North Tantau Avenue. Cupertino, CA 12345. (000) 1234567'
    ];
    $sheet->writeRow($id, 0, $data);
}

$xls->close();
```

## Format usage
```php
$xls = new Spreadsheet_Excel_Writer();

$titleFormat = $xls->addFormat(); 
$titleFormat->setFontFamily('Helvetica');
$titleFormat->setBold();
$titleFormat->setSize(10);
$titleFormat->setColor('orange'); 
$titleFormat->setBorder(1);
$titleFormat->setBottom(2);
$titleFormat->setBottomColor(44);
$titleFormat->setAlign('center');

$sheet = $xls->addWorksheet('info'); 

$sheet->write(0, 0, 'Text 123', $titleFormat);
```

## Header usage (Sending HTTP header for download dialog)
```php
$xls = new Spreadsheet_Excel_Writer();
$xls->send('excel_'.date("Y-m-d__H:i:s").'.xls');
```


# Performance

**Platform:**  
Intel(R) Core(TM) i5-4670 CPU @ 3.40GHz  
PHP 7.4    

**Test case:**  
Write xls (BIFF8 format, UTF-8), by 5 cells (1x number, 4x string without format/styles, average line length = 120 char) in each row  

**Estimated performance:**  

| Number of rows | Time (seconds) | Peak memory usage (MB) |
| --- | --- | --- |
| 10000 | 0.2 | 4 |
| 20000 | 0.4 | 4 |
| 30000 | 0.6 | 6 |
| 40000 | 0.8 | 6 |
| 50000 | 1.0 | 8 |
| 65534 | 1.2 | 8 |

# Alternative solutions

- [PHPOffice/PhpSpreadsheet](https://github.com/PHPOffice/PhpSpreadsheet)  
File formats supported: https://phpspreadsheet.readthedocs.io/en/latest/  
- [box/spout](https://github.com/box/spout)  
File formats supported: https://opensource.box.com/spout/  
