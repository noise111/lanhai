<?php

/**
 * Created by we7xq.
 * User: zhoufeng
 * Time: 2017/6/21 下午9:07
 */
class model_execl
{
    static function column_str($key)
    {
        $array = array(
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H',
            'I',
            'J',
            'K',
            'L',
            'M',
            'N',
            'O',
            'P',
            'Q',
            'R',
            'S',
            'T',
            'U',
            'V',
            'W',
            'X',
            'Y',
            'Z',
            'AA',
            'AB',
            'AC',
            'AD',
            'AE',
            'AF',
            'AG',
            'AH',
            'AI',
            'AJ',
            'AK',
            'AL',
            'AM',
            'AN',
            'AO',
            'AP',
            'AQ',
            'AR',
            'AS',
            'AT',
            'AU',
            'AV',
            'AW',
            'AX',
            'AY',
            'AZ',
            'BA',
            'BB',
            'BC',
            'BD',
            'BE',
            'BF',
            'BG',
            'BH',
            'BI',
            'BJ',
            'BK',
            'BL',
            'BM',
            'BN',
            'BO',
            'BP',
            'BQ',
            'BR',
            'BS',
            'BT',
            'BU',
            'BV',
            'BW',
            'BX',
            'BY',
            'BZ',
            'CA',
            'CB',
            'CC',
            'CD',
            'CE',
            'CF',
            'CG',
            'CH',
            'CI',
            'CJ',
            'CK',
            'CL',
            'CM',
            'CN',
            'CO',
            'CP',
            'CQ',
            'CR',
            'CS',
            'CT',
            'CU',
            'CV',
            'CW',
            'CX',
            'CY',
            'CZ'
        );
        return $array[$key];
    }
    static function column($key, $columnnum = 1)
    {
        return self::column_str($key) . $columnnum;
    }
    static function export($list, $params = array())
    {
        if (PHP_SAPI == 'cli') {
            die('This example should only be run from a Web Browser');
        }
        require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';
        $excel = new PHPExcel();
        $excel->getProperties()->setCreator("小区秘书")->setLastModifiedBy("小区秘书")->setTitle("Office 2007 XLSX Test Document")->setSubject("Office 2007 XLSX Test Document")->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")->setKeywords("office 2007 openxml php")->setCategory("report file");
        $sheet  = $excel->setActiveSheetIndex(0);
        $rownum = 1;
        foreach ($params['columns'] as $key => $column) {
            $sheet->setCellValue(self::column($key, $rownum), $column['title']);
            if (!empty($column['width'])) {
                $sheet->getColumnDimension(self::column_str($key))->setWidth($column['width']);
            }
        }
        $rownum++;
        foreach ($list as $row) {
            $len = count($row);
            for ($i = 0; $i < $len; $i++) {
                $value = $row[$params['columns'][$i]['field']].' ';
                $sheet->setCellValue(self::column($i, $rownum), $value);
            }
            $rownum++;
        }
        $excel->getActiveSheet()->setTitle($params['title']);
        $filename = urlencode($params['title']);
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment;filename="' . $filename . '.xls"');
        header('Cache-Control: max-age=0');
        $writer = PHPExcel_IOFactory::createWriter($excel, 'Excel5');
        $writer->save('php://output');
        exit;
    }
    static function import($excefile)
    {
        global $_W;
        require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';
        require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel/IOFactory.php';
        require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel/Reader/Excel5.php';
//        $path = IA_ROOT . "/addons/".MODULE_NAME."/template/upFile/";
        $time = date('Ymd',TIMESTAMP);
        $path = IA_ROOT . "/addons/".MODULE_NAME."/data/files/excel/temp_".$_W['uniacid']."/".$time."/";
        if (!is_dir($path)) {
            load()->func('file');
            mkdirs($path, '0777');
        }
        $file     = time() . $_W['uniacid'] . ".xlsx";
        $filename = $_FILES[$excefile]['name'];
        $tmpname  = $_FILES[$excefile]['tmp_name'];
        if (empty($tmpname)) {
            if ($_W['isajax']){
                echo json_encode(array('content' => '请选择要上传的Excel文件!'));exit();
            }else {
                itoast('请选择要上传的Excel文件!', '', 'error');
            }
        }
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        if ($ext != 'xlsx' && $ext != 'xls') {
            if ($_W['isajax']){
                echo json_encode(array('content' => '请上传 xlsx 格式的Excel文件!'));exit();
            }
            itoast('请上传 xlsx 格式的Excel文件!', '', 'error');
        }
        $uploadfile = $path . $file;
        $result     = move_uploaded_file($tmpname, $uploadfile);
        if (!$result) {
            if ($_W['isajax']){
                echo json_encode(array('content' => '上传Excel 文件失败, 请重新上传!'));exit();
            }
            itoast('上传Excel 文件失败, 请重新上传!', '', 'error');
        }
        if ($ext == 'xlsx'){
            $reader             = PHPExcel_IOFactory::createReader('Excel2007');
        }elseif ($ext == 'xls'){
            $reader             = PHPExcel_IOFactory::createReader('Excel5');
        }

        $excel              = $reader->load($uploadfile);
        $sheet              = $excel->getActiveSheet();
        $highestRow         = $sheet->getHighestRow();
        $highestColumn      = $sheet->getHighestColumn();
        $highestColumnCount = PHPExcel_Cell::columnIndexFromString($highestColumn);
        $values             = array();
        for ($row = 1; $row <= $highestRow; $row++) {
            $rowValue = array();
            for ($col = 0; $col < $highestColumnCount; $col++) {
                $rowValue[] = $sheet->getCellByColumnAndRow($col, $row)->getCalculatedValue();
            }
            $values[] = $rowValue;
        }
        return $values;
    }

    static public function read($excefile, $encode = 'utf-8')
    {
        global $_W;
        /*设置上传路径*/
        $time = date('Ymd',TIMESTAMP);
        $path = IA_ROOT . "/addons/".MODULE_NAME."/data/files/excel/temp_".$_W['uniacid']."/".$time."/";
        if (!is_dir($path)) {
            load()->func('file');
            mkdirs($path, '0777');
        }
        $filename = $_FILES[$excefile]['name'];

        $tmpname  = $_FILES[$excefile]['tmp_name'];

        $file_types = explode(".", $filename);
        $file_type = $file_types[count($file_types) - 1];
        /*判别是不是.xls文件，判别是不是excel文件*/
        if (strtolower($file_type) != "xls" && strtolower($file_type) != "xlsx") {
            if ($_W['isajax']){
                echo json_encode(array('content' => '类型不正确，请重新上传'));exit();
            }else{
                itoast('类型不正确，请重新上传', referer(), 'error', ture);
            }

        }
        if (empty($tmpname)) {
            if ($_W['isajax']){
                echo json_encode(array('content' => '请选择要上传的Excel文件'));exit();
            }else{
                itoast('请选择要上传的Excel文件!', '', 'error');
            }
        }

//        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
//        if ($ext != 'xlsx' && $ext != 'xls') {
//            if ($_W['isajax']){
//                echo json_encode(array('content' => '上传 xlsx 格式的Excel文件!'));exit();
//            }else{
//                itoast('请上传 xlsx 格式的Excel文件!', '', 'error');
//            }
//        }



        /*以时间来命名上传的文件*/
        $str = date('Ymdhis');
        $file_name = $str . "." . $file_type;
        /*是否上传成功*/
        if (!copy($tmpname, $path.$file_name)) {
            if ($_W['isajax']) {
                echo json_encode(array('content' => '上传失败'));
                exit();
            }else{
                echo json_encode(array('content' => '上传失败'));
            }
        }
        require_once IA_ROOT . '/framework/library/phpexcel/PHPExcel.php';
        $objPHPExcel = new PHPExcel();
        $objPHPExcel = PHPExcel_IOFactory::load($path.$file_name);
        $indata = $objPHPExcel->getSheet(0)->toArray();
        return $indata;

    }

}