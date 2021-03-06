<?php
/**
 * This view exports the monthly presence report
 * It builds a Spreadsheet file downloaded by the browser.
 * @copyright  Copyright (c) 2019-2020 Karl Joseph Saycon
 * @license      http://opensource.org/licenses/AGPL-3.0 AGPL-3.0
 * @link            https://github.com/ninjakwarl
 * @since         0.4.3
 */
        
//Details about the employee
$employee_name =  $employee['firstname'] . ' ' . $employee['lastname'];
$contract = $this->contracts_model->getContracts($employee['contract']);
if (!empty($contract)) {
    $contract_name = $contract['name'];
} else {
    $contract_name = '';
}

//Compute facts about dates and the selected month
if ($month == 0) $month = date('m', strtotime('last month'));
if ($year == 0) $year = date('Y', strtotime('last month'));
$total_days = cal_days_in_month(CAL_GREGORIAN, $month, $year);
$start = sprintf('%d-%02d-01', $year, $month);
$lastDay = date("t", strtotime($start));    //last day of selected month
$end = sprintf('%d-%02d-%02d', $year, $month, $lastDay);
//Number of non working days during the selected month
$non_working_days = $this->dayoffs_model->lengthDaysOffBetweenDates($employee['contract'], $start, $end);
$opened_days = $total_days - $non_working_days;
$month_name = lang(date('F', strtotime($start)));

//tabular view of the leaves
$linear = $this->leaves_model->linear($id, $month, $year, FALSE, FALSE, TRUE, FALSE);
$leave_duration = $this->leaves_model->monthlyLeavesDuration($linear);
$work_duration = $opened_days - $leave_duration;
$leaves_detail = $this->leaves_model->monthlyLeavesByType($linear);
//Leave balance of the employee
$summary = $this->leaves_model->getLeaveBalanceForEmployee($id, FALSE, $end);

//Print the header with the facts of the presence report
$sheet = $this->excel->setActiveSheetIndex(0);
$sheet->setTitle(mb_strimwidth(lang('hr_presence_title'), 0, 28, "..."));  //Maximum 31 characters allowed in sheet title.

$sheet->setCellValue('A1', lang('hr_presence_employee'));
$sheet->setCellValue('A2', lang('hr_presence_month'));
$sheet->setCellValue('A3', lang('hr_presence_days'));
$sheet->setCellValue('A4', lang('hr_presence_contract'));
$sheet->setCellValue('A5', lang('hr_presence_working_days'));
$sheet->setCellValue('A6', lang('hr_presence_non_working_days'));
$sheet->setCellValue('A7', lang('hr_presence_work_duration'));
$sheet->setCellValue('A8', lang('hr_presence_leave_duration'));
$sheet->getStyle('A1:A8')->getFont()->setBold(true);

$sheet->setCellValue('B1', $employee_name);
$sheet->setCellValue('B2', $month_name);
$sheet->setCellValue('B3', $total_days);
$sheet->setCellValue('B4', $contract_name);
$sheet->setCellValue('B5', $opened_days);
$sheet->setCellValue('B6', $non_working_days);
$sheet->setCellValue('B7', $work_duration);
$sheet->setCellValue('B8', $leave_duration);

if (count($leaves_detail) > 0) {
    $line = 9;
    foreach ($leaves_detail as $leaves_type_name => $leaves_type_sum) {
        $sheet->setCellValue('A' . $line, $leaves_type_name);
        $sheet->setCellValue('B' . $line, $leaves_type_sum);
        $sheet->getStyle('A' . $line)->getAlignment()->setIndent(2);
        $line++;
    }
}

//Print two lines : the short name of all days for the selected month (horizontally aligned)
$start = $year . '-' . $month . '-' . '1';    //first date of selected month
$lastDay = date("t", strtotime($start));    //last day of selected month
for ($ii = 1; $ii <=$lastDay; $ii++) {
    $dayNum = date("N", strtotime($year . '-' . $month . '-' . $ii));
    $col = $this->excel->column_name(3 + $ii);
    //Print day number
    $sheet->setCellValue($col . '11', $ii);
    //Print short name of the day
    switch ($dayNum)
    {
        case 1: $sheet->setCellValue($col . '10', lang('calendar_monday_short')); break;
        case 2: $sheet->setCellValue($col . '10', lang('calendar_tuesday_short')); break;
        case 3: $sheet->setCellValue($col . '10', lang('calendar_wednesday_short')); break;
        case 4: $sheet->setCellValue($col . '10', lang('calendar_thursday_short')); break;
        case 5: $sheet->setCellValue($col . '10', lang('calendar_friday_short')); break;
        case 6: $sheet->setCellValue($col . '10', lang('calendar_saturday_short')); break;
        case 7: $sheet->setCellValue($col . '10', lang('calendar_sunday_short')); break;
    }
}
//The header is horizontally aligned
$col = $this->excel->column_name(3 + $lastDay);
$sheet->getStyle('C8:' . $col . '9')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

//Box around the lines for each employee
$styleBox = array(
    'borders' => array(
        'top' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        ),
        'bottom' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN
        )
    )
  );

$dayBox =  array(
    'borders' => array(
        'left' => array(
            'style' => PHPExcel_Style_Border::BORDER_DASHDOT,
            'rgb' => '808080'
        ),
        'right' => array(
            'style' => PHPExcel_Style_Border::BORDER_DASHDOT,
            'rgb' => '808080'
        )
    )
 );

//Background colors for the calendar according to the type of leave
$styleBgPlanned = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'DDD')
    )
);
$styleBgRequested = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'F89406')
    )
);
$styleBgAccepted = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => '468847')
    )
);
$styleBgRejected = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'FF0000')
    )
);
$styleBgDayOff = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => '000000')
    )
);

$line = 12;
$col = $this->excel->column_name($lastDay + 3);
$sheet->getStyle('D' . $line . ':' . $col . ($line + 1))->applyFromArray($styleBox);

//Iterate on all days of the selected month
$dayNum = 0;
foreach ($linear->days as $day) {
    $dayNum++;
    $col = $this->excel->column_name(3 + $dayNum);
    if (strstr($day->display, ';')) {//Two statuses in the cell
        $statuses = explode(";", $day->status);
        $types = explode(";", $day->type);
            //0 - Working day  _
            //1 - All day           []
            //2 - Morning        |\
            //3 - Afternoon      /|
            //4 - All Day Off       []
            //5 - Morning Day Off   |\
            //6 - Afternoon Day Off /|
          $sheet->getComment($col . $line)->getText()->createTextRun($types[0]);
          $sheet->getComment($col . ($line + 1))->getText()->createTextRun($types[1]);
          switch (intval($statuses[1]))
          {
            case 1: $sheet->getStyle($col . $line)->applyFromArray($styleBgPlanned); break;  // Planned
            case 2: $sheet->getStyle($col . $line)->applyFromArray($styleBgRequested); break;  // Requested
            case 3: $sheet->getStyle($col . $line)->applyFromArray($styleBgAccepted); break;  // Accepted
            case 4: $sheet->getStyle($col . $line)->applyFromArray($styleBgRejected); break;  // Rejected
            case '5': $sheet->getStyle($col . $line)->applyFromArray($styleBgDayOff); break;    //Day off
            case '6': $sheet->getStyle($col . $line)->applyFromArray($styleBgDayOff); break;    //Day off
          }
          switch (intval($statuses[0]))
          {
            case 1: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgPlanned); break;  // Planned
            case 2: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgRequested); break;  // Requested
            case 3: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgAccepted); break;  // Accepted
            case 4: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgRejected); break;  // Rejected
            case '5': $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgDayOff); break;    //Day off
            case '6': $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgDayOff); break;    //Day off
          }//Two statuses in the cell
    } else {//Only one status in the cell
        switch ($day->display) {
            case '1':   //All day
                    $sheet->getComment($col . $line)->getText()->createTextRun($day->type);
                    $sheet->getComment($col . ($line + 1))->getText()->createTextRun($day->type);
                    switch ($day->status)
                    {
                        // 1 : 'Planned';
                        // 2 : 'Requested';
                        // 3 : 'Accepted';
                        // 4 : 'Rejected';
                        case 1: $sheet->getStyle($col . $line . ':' . $col . ($line + 1))->applyFromArray($styleBgPlanned); break;  // Planned
                        case 2: $sheet->getStyle($col . $line . ':' . $col . ($line + 1))->applyFromArray($styleBgRequested); break; // Requested
                        case 3: $sheet->getStyle($col . $line . ':' . $col . ($line + 1))->applyFromArray($styleBgAccepted); break;  // Accepted
                        case 4: $sheet->getStyle($col . $line . ':' . $col . ($line + 1))->applyFromArray($styleBgRejected); break;  // Rejected
                    }
                    break;
            case '2':   //AM
                $sheet->getComment($col . $line)->getText()->createTextRun($day->type);
                switch ($day->status)
                  {
                      case 1: $sheet->getStyle($col . $line)->applyFromArray($styleBgPlanned); break;  // Planned
                      case 2: $sheet->getStyle($col . $line)->applyFromArray($styleBgRequested); break;  // Requested
                      case 3: $sheet->getStyle($col . $line)->applyFromArray($styleBgAccepted); break;  // Accepted
                      case 4: $sheet->getStyle($col . $line)->applyFromArray($styleBgRejected); break;  // Rejected
                  }
                break;
            case '3':   //PM
                $sheet->getComment($col . ($line + 1))->getText()->createTextRun($day->type);
                switch ($day->status)
                  {
                      case 1: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgPlanned); break;  // Planned
                      case 2: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgRequested); break;  // Requested
                      case 3: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgAccepted); break;  // Accepted
                      case 4: $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgRejected); break;  // Rejected
                  }
                break;
            case '4': //Full day off
                $sheet->getStyle($col . $line . ':' . $col . ($line + 1))->applyFromArray($styleBgDayOff);
                $sheet->getComment($col . $line)->getText()->createTextRun($day->type);
                $sheet->getComment($col . ($line + 1))->getText()->createTextRun($day->type);
                break;
            case '5':  //AM off
                $sheet->getStyle($col . $line)->applyFromArray($styleBgDayOff);
                $sheet->getComment($col . $line)->getText()->createTextRun($day->type);
                break;
            case '6':   //PM off
                $sheet->getStyle($col . ($line + 1))->applyFromArray($styleBgDayOff);
                $sheet->getComment($col . ($line + 1))->getText()->createTextRun($day->type);
                break;
        }
      }//Only one status in the cell
}//day

//Autofit for all column containing the days
for ($ii = 1; $ii <=$lastDay; $ii++) {
    $col = $this->excel->column_name($ii + 3);
    $sheet->getStyle($col . '10:' . $col . '13')->applyFromArray($dayBox);
    $sheet->getColumnDimension($col)->setAutoSize(TRUE);
}
$sheet->getColumnDimension('A')->setAutoSize(TRUE);
$sheet->getColumnDimension('B')->setAutoSize(TRUE);

//Leave Balance
$sheet->setCellValue('C16', lang('hr_summary_thead_type'));
$sheet->setCellValue('J16', lang('hr_summary_thead_available'));
$sheet->setCellValue('P16', lang('hr_summary_thead_taken'));
$sheet->setCellValue('V16', lang('hr_summary_thead_entitled'));
$sheet->setCellValue('AB16', lang('hr_summary_thead_description'));
$sheet->getStyle('C16:AH16')->getFont()->setBold(true);
$sheet->mergeCells('C16:I16');
$sheet->mergeCells('J16:O16');
$sheet->mergeCells('P16:U16');
$sheet->mergeCells('V16:AA16');
$sheet->mergeCells('AB16:AK16');

$line = 17;
foreach ($summary as $key => $value) {
    $sheet->setCellValue('C' . $line, $key);
    $sheet->setCellValue('J' . $line, ((float) $value[1] - (float) $value[0]));
    if ($value[2] == '') {
        $sheet->setCellValue('P' . $line, ((float) $value[0]));
    } else {
        $sheet->setCellValue('P' . $line, '-');
    }
    if ($value[2] == '') {
        $sheet->setCellValue('V' . $line, ((float) $value[1]));
    } else {
        $sheet->setCellValue('V' . $line, '-');
    }
    $sheet->setCellValue('AB' . $line, $value[2]);

    $sheet->getStyle('C' . $line . ':AK' . $line)->applyFromArray($styleBox);
    $sheet->mergeCells('C' . $line . ':I' . $line);
    $sheet->mergeCells('J' . $line . ':O' . $line);
    $sheet->mergeCells('P' . $line . ':U' . $line);
    $sheet->mergeCells('V' . $line . ':AA' . $line);
    $sheet->mergeCells('AB' . $line . ':AK' . $line);

    $line++;
}

//Set layout to landscape and make the Excel sheet fit to the page
$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
$sheet->getPageSetup()->setPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
$sheet->getPageSetup()->setFitToPage(true);
$sheet->getPageSetup()->setFitToWidth(1);
$sheet->getPageSetup()->setFitToHeight(0);

exportSpreadsheet($this, 'presence');
