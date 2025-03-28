<?php

use Intervention\Image\ImageManagerStatic as Image;

const MONTH_LONG_NAMES = [
    '01' => 'มกราคม',
    '02' => 'กุมภาพันธ์',
    '03' => 'มีนาคม',
    '04' => 'เมษายน',
    '05' => 'พฤษภาคม',
    '06' => 'มิถุนายน',
    '07' => 'กรกฎาคม',
    '08' => 'สิงหาคม',
    '09' => 'กันยายน',
    '10' => 'ตุลาคม',
    '11' => 'พฤศจิกายน',
    '12' => 'ธันวาคม',
];

const MONTH_SHORT_NAMES = [
    '01' => 'ม.ค.',
    '02' => 'ก.พ.',
    '03' => 'มี.ค.',
    '04' => 'เม.ย',
    '05' => 'พ.ค.',
    '06' => 'มิ.ย.',
    '07' => 'ก.ค.',
    '08' => 'ส.ค.',
    '09' => 'ก.ย.',
    '10' => 'ต.ค.',
    '11' => 'พ.ย.',
    '12' => 'ธ.ค.',
];

static $PAPER_SIZES = array(
    "4a0" => array(0, 0, 4767.87, 6740.79),
    "2a0" => array(0, 0, 3370.39, 4767.87),
    "a0" => array(0, 0, 2383.94, 3370.39),
    "a1" => array(0, 0, 1683.78, 2383.94),
    "a2" => array(0, 0, 1190.55, 1683.78),
    "a3" => array(0, 0, 841.89, 1190.55),
    "a4" => array(0, 0, 595.28, 841.89),
    "a5" => array(0, 0, 419.53, 595.28),
    "a6" => array(0, 0, 297.64, 419.53),
    "a7" => array(0, 0, 209.76, 297.64),
    "a8" => array(0, 0, 147.40, 209.76),
    "a9" => array(0, 0, 104.88, 147.40),
    "a10" => array(0, 0, 73.70, 104.88),
    "b0" => array(0, 0, 2834.65, 4008.19),
    "b1" => array(0, 0, 2004.09, 2834.65),
    "b2" => array(0, 0, 1417.32, 2004.09),
    "b3" => array(0, 0, 1000.63, 1417.32),
    "b4" => array(0, 0, 708.66, 1000.63),
    "b5" => array(0, 0, 498.90, 708.66),
    "b6" => array(0, 0, 354.33, 498.90),
    "b7" => array(0, 0, 249.45, 354.33),
    "b8" => array(0, 0, 175.75, 249.45),
    "b9" => array(0, 0, 124.72, 175.75),
    "b10" => array(0, 0, 87.87, 124.72),
    "c0" => array(0, 0, 2599.37, 3676.54),
    "c1" => array(0, 0, 1836.85, 2599.37),
    "c2" => array(0, 0, 1298.27, 1836.85),
    "c3" => array(0, 0, 918.43, 1298.27),
    "c4" => array(0, 0, 649.13, 918.43),
    "c5" => array(0, 0, 459.21, 649.13),
    "c6" => array(0, 0, 323.15, 459.21),
    "c7" => array(0, 0, 229.61, 323.15),
    "c8" => array(0, 0, 161.57, 229.61),
    "c9" => array(0, 0, 113.39, 161.57),
    "c10" => array(0, 0, 79.37, 113.39),
    "ra0" => array(0, 0, 2437.80, 3458.27),
    "ra1" => array(0, 0, 1729.13, 2437.80),
    "ra2" => array(0, 0, 1218.90, 1729.13),
    "ra3" => array(0, 0, 864.57, 1218.90),
    "ra4" => array(0, 0, 609.45, 864.57),
    "sra0" => array(0, 0, 2551.18, 3628.35),
    "sra1" => array(0, 0, 1814.17, 2551.18),
    "sra2" => array(0, 0, 1275.59, 1814.17),
    "sra3" => array(0, 0, 907.09, 1275.59),
    "sra4" => array(0, 0, 637.80, 907.09),
    "letter" => array(0, 0, 612.00, 792.00),
    "legal" => array(0, 0, 612.00, 1008.00),
    "ledger" => array(0, 0, 1224.00, 792.00),
    "tabloid" => array(0, 0, 792.00, 1224.00),
    "executive" => array(0, 0, 521.86, 756.00),
    "folio" => array(0, 0, 612.00, 936.00),
    "commercial #10 envelope" => array(0, 0, 684, 297),
    "catalog #10 1/2 envelope" => array(0, 0, 648, 864),
    "8.5x11" => array(0, 0, 612.00, 792.00),
    "8.5x14" => array(0, 0, 612.00, 1008.0),
    "11x17" => array(0, 0, 792.00, 1224.00),
);

function uploadFile($file, $destPath)
{
    $filename = '';
    if ($file) {
        $filename = date('mdYHis') . uniqid(). '.' .$file->getClientOriginalExtension();

        $file->move($destPath, $filename);
    }

    return $filename;
}

function uploadThumbnail($img, $destPath)
{
    $img_name = '';
    if ($img) {
        $img_name = date('mdYHis') . uniqid(). '.' .$img->getClientOriginalExtension();

        $img_resized = Image::make($img->getRealPath());
        $img_resized->resize(300, null, function($constraint) {
            $constraint->aspectRatio();
        });
        $img_resized->save(public_path($destPath. '/' .$img_name));
    }

    return $img_name;
}

function convDbDateToThDate($dbDate)
{
    if(empty($dbDate)) return '';

    $arrDate = explode('-', $dbDate);

    return $arrDate[2]. '/' .$arrDate[1]. '/' .((int)$arrDate[0] + 543);
}

function convThDateToDbDate($dbDate)
{
    if(empty($dbDate)) return '';

    $arrDate = explode('/', $dbDate);

    return ((int)$arrDate[2] - 543). '-' .$arrDate[1]. '-' .$arrDate[0];
}

function calcBudgetYear($sdate)
{
    $budgetYear = date('Y') + 543;
    list($day, $month, $year) = explode('/', $sdate);

    if ((int)$month >= 10) {
        $budgetYear = (int)$year + 1;
    } else {
        $budgetYear = (int)$year;
    }

    return $budgetYear;
}

function getShortMonth($monthDigits)
{
    return MONTH_SHORT_NAMES[$monthDigits];
}

function convDbDateToLongThDate($dbDate)
{
    if(empty($dbDate)) return '';

    $arrDate = explode('-', $dbDate);

    return (int)$arrDate[2]. ' ' .MONTH_LONG_NAMES[$arrDate[1]]. ' ' .((int)$arrDate[0] + 543);
}

function convDbDateToLongThMonth($dbDate)
{
    if(empty($dbDate)) return '';

    $arrDate = explode('-', $dbDate);

    return MONTH_LONG_NAMES[$arrDate[1]]. ' ' .((int)$arrDate[0] + 543);
}

function convDbDateToLongThDateRange($dbSDate, $dbEDate)
{
    if(empty($dbSDate)) return '';

    $arrSDate = explode('-', $dbSDate);

    if (!$dbEDate || ($dbEDate && $dbSDate == $dbEDate)) {
        return (int)$arrSDate[2]. ' ' .MONTH_LONG_NAMES[$arrSDate[1]]. ' ' .((int)$arrSDate[0] + 543);
    } else {
        $arrEDate = explode('-', $dbEDate);

        if ($arrSDate[0] == $arrEDate[0] && $arrSDate[1] == $arrEDate[1]) {
            return (int)$arrSDate[2].'-'.(int)$arrEDate[2]. ' ' .MONTH_LONG_NAMES[$arrSDate[1]]. ' ' .((int)$arrSDate[0] + 543);
        } else {
            return (int)$arrSDate[2]. ' ' .MONTH_LONG_NAMES[$arrSDate[1]]. ' ' .((int)$arrSDate[0] + 543).'-'.(int)$arrEDate[2]. ' ' .MONTH_LONG_NAMES[$arrEDate[1]]. ' ' .((int)$arrEDate[0] + 543);
        }
    }
}

/**
 * $renderType should be 'preview' | 'download'
 */
function renderPdf($view, $data, $paper = null, $renderType = 'preview')
{
    $pdf = PDF::loadView($view, $data);

    /** Set font directory */
    // $pdf->setOption('fontDir', public_path('/fonts'));

    if ($paper) {
        $pdf->setPaper($paper['size'], $paper['orientation']);
    }

    /** แบบนี้จะ stream มา preview */
    if ($renderType == 'preview') {
        return $pdf->stream();
    }

    /** แบบนี้จะดาวโหลดเลย */
    return $pdf->download(date('dmY').'-'.uniqid().'.pdf');
}

function thainumDigit($num){
    return str_replace(
        array( '0' , '1' , '2' , '3' , '4' , '5' , '6' ,'7' , '8' , '9' ),
        array( "o" , "๑" , "๒" , "๓" , "๔" , "๕" , "๖" , "๗" , "๘" , "๙" ),
        $num
    );
}

function currencyToNumber($currency) {
    if ($currency == '') return 0;

    return str_replace(',', '', $currency);
}

const BAHT_TEXT_NUMBERS = array('ศูนย์', 'หนึ่ง', 'สอง', 'สาม', 'สี่', 'ห้า', 'หก', 'เจ็ด', 'แปด', 'เก้า');
const BAHT_TEXT_UNITS = array('', 'สิบ', 'ร้อย', 'พัน', 'หมื่น', 'แสน', 'ล้าน');
const BAHT_TEXT_ONE_IN_TENTH = 'เอ็ด';
const BAHT_TEXT_TWENTY = 'ยี่';
const BAHT_TEXT_INTEGER = 'ถ้วน';
const BAHT_TEXT_BAHT = 'บาท';
const BAHT_TEXT_SATANG = 'สตางค์';
const BAHT_TEXT_POINT = 'จุด';

/**
 * Convert baht number to Thai text
 * @param double|int $number
 * @param bool $include_unit
 * @param bool $display_zero
 * @return string|null
 */
function baht_text($number, $include_unit = true, $display_zero = true)
{
    if (!is_numeric($number)) {
        return null;
    }

    $log = floor(log($number, 10));
    if ($log > 5) {
        $millions = floor($log / 6);
        $million_value = pow(1000000, $millions);
        $normalised_million = floor($number / $million_value);
        $rest = $number - ($normalised_million * $million_value);
        $millions_text = '';
        for ($i = 0; $i < $millions; $i++) {
            $millions_text .= BAHT_TEXT_UNITS[6];
        }
        return baht_text($normalised_million, false) . $millions_text . baht_text($rest, true, false);
    }

    $number_str = (string)floor($number);
    $text = '';
    $unit = 0;

    if ($display_zero && $number_str == '0') {
        $text = BAHT_TEXT_NUMBERS[0];
    } else for ($i = strlen($number_str) - 1; $i > -1; $i--) {
        $current_number = (int)$number_str[$i];

        $unit_text = '';
        if ($unit == 0 && $i > 0) {
            $previous_number = isset($number_str[$i - 1]) ? (int)$number_str[$i - 1] : 0;
            if ($current_number == 1 && $previous_number > 0) {
                $unit_text .= BAHT_TEXT_ONE_IN_TENTH;
            } else if ($current_number > 0) {
                $unit_text .= BAHT_TEXT_NUMBERS[$current_number];
            }
        } else if ($unit == 1 && $current_number == 2) {
            $unit_text .= BAHT_TEXT_TWENTY;
        } else if ($current_number > 0 && ($unit != 1 || $current_number != 1)) {
            $unit_text .= BAHT_TEXT_NUMBERS[$current_number];
        }

        if ($current_number > 0) {
            $unit_text .= BAHT_TEXT_UNITS[$unit];
        }

        $text = $unit_text . $text;
        $unit++;
    }

    if ($include_unit) {
        $text .= BAHT_TEXT_BAHT;

        $satang = explode('.', number_format($number, 2, '.', ''))[1];
        $text .= $satang == 0
            ? BAHT_TEXT_INTEGER
            : baht_text($satang, false) . BAHT_TEXT_SATANG;
    } else {
        $exploded = explode('.', $number);
        if (isset($exploded[1])) {
            $text .= BAHT_TEXT_POINT;
            $decimal = (string)$exploded[1];
            for ($i = 0; $i < strlen($decimal); $i++) {
                $text .= BAHT_TEXT_NUMBERS[$decimal[$i]];
            }
        }
    }

    return $text;
}

function isRenderWardInsteadDepart($departId) {
    return in_array($departId, ['19','20','68']);
}

/** ตรวจสอบคณะกรรมการการขอสนับสนุน */
function committeeNumber($committees, $type) {
    $number = 0;

    foreach($committees as $committee) {
        if($committee->committee_type_id == $type) {
            $number++;
        }
    }

    return $number;
}

// function exportExcel($fileName, $view, $data, $options)
// {
//     return \Excel::create($fileName, function($excel) use ($view, $data, $options) {
//         $excel->sheet('sheet1', function($sheet) use ($view, $data, $options)
//         {
//             $sheet->loadView($view, [
//                 'data' => $data,
//                 'options' => $options
//             ]);                
//         });
//     })->download();
// }

function array_any(array $array, callable $fn) {
    foreach ($array as $value) {
        if($fn($value)) {
            return true;
        }
    }
    return false;
}

function array_every(array $array, callable $fn) {
    foreach ($array as $value) {
        if(!$fn($value)) {
            return false;
        }
    }
    return true;
}

function replaceExpensePattern($pattern = '', $replacement = '') {
    list($p_amount, $p_time, $p_price) = explode('X', $pattern);
    list($r_amount, $r_time, $r_price) = explode('*', $replacement);

    return str_replace('...', $r_amount, $p_amount) . 'X' . str_replace('...', $r_time, $p_time) . 'X' . str_replace('...', $r_price, $p_price);
}

function replaceExpensePatternFromDesc($pattern = '', $replacement = '') {
    if (strpos($replacement, '+') != false) {
        $groups = array_map(function($group) use ($pattern) { return replaceExpensePattern($pattern, $group); }, explode('+', $replacement));

        return join('+', $groups);
    } else {
        return replaceExpensePattern($pattern, $replacement);
    }
}

function formatCurrency(array $inputs, array $fields) {
    foreach ($inputs as $key => $val) {
        if (array_any($fields, function ($field) use ($key) { return $field == $key; })) {
            $inputs[$key] = currencyToNumber($val);
        }
    }

    return $inputs;
}

function transformManyInputs(array $data, array $fields, array $addOptions) {
    return array_map(function($item) use ($fields, $addOptions) {
        $filtered = Arr::only($item, $fields);

        foreach ($addOptions as $key => $val) {
            $filtered = Arr::add($filtered, $key, $val);
        }

        return $filtered;
    }, $data);
}
