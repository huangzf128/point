<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use App\Common\Consts;
use App\Model\CategoryModel;
use App\Model\ItemModel;
use App\Model\CustomerModel;
use App\Model\WarehouseModel;
use App\Model\InventoryModel;
use App\Model\OutHistoryModel;


/**
 * Action
 */
final class ReportAction extends BaseAction
{
    protected $container;
	protected $excelRoot;
	protected $reportRoot;

    public function __construct(ContainerInterface $container)
    {
		parent::__construct($container);
        $this->container = $container;
		$this->excelRoot = $container->get('settings')['root']."/excel/";
		$this->reportRoot = $container->get('settings')['root']."/reports/";
    }

    public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$params = $request->getQueryParams();
		$ym = $params['ym'] ?? date('Y-m');
		$customerId = $params['customerId'] ?? "";

		$itemModel = new ItemModel($this->container);
		$items = $itemModel->getItem();

		$customerModel = new CustomerModel($this->container);
		$customers = $customerModel->getCustomer();

		$customerName = $this->getCustomerName($customers, $customerId);

		return $this->container->get('view')->render($response, 'report.html', 
				['items' => $items, 'ym' => $ym,
				'customers' => $customers, 'customerId' => $customerId, 'customerName' => $customerName ?? ""]);
    }

    public function indexTotal(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$params = $request->getQueryParams();
		$ym = $params['ym'] ?? date('Y-m');
		$customerId = $params['customerId'] ?? "";
		$reportId = $params['reportId'] ?? "";

		$customerModel = new CustomerModel($this->container);
		$customers = $customerModel->getCustomer();

		$invHistorys = [];
		if (!empty($customerId) && !empty($ym)) {
			$invHistoryModel = new OutHistoryModel($this->container);
			$invHistorys = $invHistoryModel->getOutHistory($ym, $customerId);
		}

		$customerName = $this->getCustomerName($customers, $customerId);

		return $this->container->get('view')->render($response, 'reportTotal.html', 
				['ym' => $ym, 'invHistorys' => $invHistorys, 'report' => $reportId,
				'customers' => $customers, 'customerId' => $customerId, 'customerName' => $customerName ?? ""]);
    }

	private function getCustomerName($customers, $customerId) {
		if(empty($customerId)) return "";

		foreach ($customers as $customer) {
			if ($customer['id'] == $customerId) {
				return $customer['name'];
			}
		}
		return "";
	}

	public function getOutHistory(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$parsedBody = $request->getParsedBody();
		$id = $parsedBody['id'];
		
		if (!empty($id)) {
			$invHistoryModel = new OutHistoryModel($this->container);
			$invItems = $invHistoryModel->getOutHistoryDetail($id);

			$payload = json_encode($invItems);

			$response->getBody()->write($payload);
			return $response->withHeader('Content-Type', 'application/json');			
		}
	}

	public function getOutHistoryDetailRange(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
	{
		$parsedBody = $request->getParsedBody();
		$customerId = $parsedBody['customerId'];
		$ym = $parsedBody['ym'];
		
		if (!empty($customerId) && !empty($ym)) {
			$invHistoryModel = new OutHistoryModel($this->container);
			$invItems = $invHistoryModel->getOutHistoryDetailRange($customerId, $ym);

			$payload = json_encode($invItems);

			$response->getBody()->write($payload);
			return $response->withHeader('Content-Type', 'application/json');			
		}
	}

	public function print(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$parsedBody = $request->getParsedBody();
		if ($parsedBody['addData']) {
			$datas = json_decode($parsedBody['addData'], true);
			$customerId = $parsedBody['customerId'];

			// --------------- for test start ---------------------
			// $merges = [];
			// for ($i = 0; $i < 7; $i++) {
			// 	foreach($datas as $key => $value) {
			// 		$merges[] = $value;
			// 	}
			// }
			// $datas = $merges;
			// ---------------- for test end -----------------------

			$customerModel = new CustomerModel($this->container);
			$customer = $customerModel->getCustomerById($customerId);

			$info = array(
				"ymd" => $parsedBody['ymd'] ?? "",
				"reportId" => $parsedBody['reportId'] ?? "",
			);

			$createTime = date('YmdHis');
			$reportType = $info['reportId'];
			if ($reportType == 1) {
				$spreadsheet = $this->estimate($datas, $customer);
				$fileName = '見積書_'.$createTime.'.xlsx';

            } else if($reportType == 2) {
				// $spreadsheet = $this->estimateRequest($datas, $customer[0]);
				// $fileName = '見積依頼書_'.$createTime.'.xlsx';

            } else if($reportType == 3) {
				// $spreadsheet = $this->order($datas, $customer);
				// $fileName = '注文書_'.$createTime.'.xlsx';

			} else if($reportType == 4) {
				$spreadsheet = $this->invoice($datas, $customer);
				$fileName = '請求書&送り状_'.$createTime.'.xlsx';

			} else if($reportType == 5) {
				$spreadsheet = $this->delivery($datas, $customer);
				$fileName = '納品書&受領書_'.$createTime.'.xlsx';

			} else if($reportType == 6) {
				// $spreadsheet = $this->shipping($datas, $customer);
				// $fileName = '出荷証明書_'.$createTime.'.xlsx';

			}

			$tempFile = $this->reportRoot.$fileName;
			$writer = new XlsxWriter($spreadsheet);
            $writer->setPreCalculateFormulas(false);
			$writer->save($tempFile);

			$response = $response->withHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;');
			$response = $response->withHeader('Content-Disposition', "attachment; filename=\"{$fileName}\"");
			$response = $response->withHeader('Cache-Control', 'max-age=0');
			$stream = fopen($tempFile, 'r+');

			return $response->withBody(new \Slim\Psr7\Stream($stream));
		}
	}

    /**
	 * 見積書
	 */
	private function estimate($datas, $customers) {

		$reader = new XlsxReader();
		$spreadsheet = $reader->load($this->excelRoot.'見積書.xlsx');
		$worksheet = $spreadsheet->getSheetByName('見積内訳');

		// 改ページための空行追加
		if (count($datas) > 42 && count($datas) <= 45) {
			$datas[] = ["serial"=>"", "itemName"=>"", "size" => "", "quantity" => "", "unit" => "", "price" => ""];
			$datas[] = ["serial"=>"", "itemName"=>"", "size" => "", "quantity" => "", "unit" => "", "price" => ""];
			$datas[] = ["serial"=>"", "itemName"=>"", "size" => "", "quantity" => "", "unit" => "", "price" => ""];
		}

		$rowNo = 22;
		foreach ($datas as $row) {

            $worksheet->setCellValue('A'.($rowNo), $row["serial"]);
			$worksheet->setCellValue('B'.($rowNo), $row["itemName"]);
			$worksheet->setCellValue('D'.($rowNo), $row["size"]);
			$worksheet->setCellValue('E'.($rowNo), $row["quantity"]);
			$worksheet->setCellValue('F'.($rowNo), $row["unit"]);
			$worksheet->setCellValue('G'.($rowNo), $row["price"]);
			$worksheet->setCellValue('H'.($rowNo), "=E".$rowNo."*G".$rowNo);

			if ($rowNo == 66 || 
				($rowNo > 66 && ($rowNo - 66) % 58 == 0) ) {

				$worksheet->setBreak("A".$rowNo, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
				$rowNo++;
			}
			$rowNo++;
		}

		if ($rowNo <= 66) {
			$lastRow = 66;
		} else {
			$lastRow = $rowNo + (58 - ($rowNo - 66) % 58);
		}

		// 合計行
		// $this->copyRows($worksheet, "A".Consts::ESTIMATE_TOTAL_ROWNO.":K".Consts::ESTIMATE_TOTAL_ROWNO, $worksheet, "A".$lastRow);
		$worksheet->getPageSetup()->setPrintArea('A1:K'.$lastRow);
		// 金額合計
		// $worksheet->setCellValue('H'.$lastRow, "=SUM(H22:H".($lastRow - 1).")");
		// 取引先名
		$worksheet->setCellValue('B3', $customers[0]['name']);

		$worksheet->removeRow($lastRow + 1, 60);

		return $spreadsheet;
	}

	/**
	 * 納品書&受領書
	 */
	private function delivery($datas, $customers) {
		$reader = new XlsxReader();
		$spreadsheet = $reader->load($this->excelRoot.'納品書&受領書.xlsx');
		$worksheet = $spreadsheet->getSheetByName('納品書');
		$worksheet1 = $spreadsheet->getSheetByName('納品書（控）');
		$worksheet2 = $spreadsheet->getSheetByName('受領書');

		// array_shift($datas);

		// 改ページための空行追加
		if (count($datas) > 17 && count($datas) <= 20) {
			$datas[] = ["serial"=>"", "itemName"=>"", "size" => "", "quantity" => "", "unit" => "", "price" => ""];
			$datas[] = ["serial"=>"", "itemName"=>"", "size" => "", "quantity" => "", "unit" => "", "price" => ""];
			$datas[] = ["serial"=>"", "itemName"=>"", "size" => "", "quantity" => "", "unit" => "", "price" => ""];
		}

		$maxCntPerPage = 20;
		$rowNo = 10;
		for ($i = 0; $i < count($datas); $i++) {
			$row = $datas[$i];

			$worksheet->setCellValue('A'.($rowNo), $row["serial"]);
			$worksheet->setCellValue('B'.($rowNo), $row["itemName"]);
			$worksheet->setCellValue('C'.($rowNo), $row["size"]);
			$worksheet->setCellValue('G'.($rowNo), $row["quantity"]);
			$worksheet->setCellValue('H'.($rowNo), $row["unit"]);
			$worksheet->setCellValue('I'.($rowNo), $row["price"]);
			$worksheet->setCellValue('J'.($rowNo), "=G".$rowNo."*I".$rowNo);

			$rowNo++;

			if (($i + 1) % $maxCntPerPage === 0) {
				$worksheet->getPageSetup()->setPrintArea('A1:M'.$rowNo);
				$worksheet1->getPageSetup()->setPrintArea('A1:M'.$rowNo);
				$worksheet2->getPageSetup()->setPrintArea('A1:M'.$rowNo);
				$rowNo += 10;
			}
		}

		// 取引先名
		$worksheet->setCellValue('B4', $customers[0]['name']);

		$rowNo--;
		$footerCnt = 1;
		if ($i > 0 && ($i + 1 - 1) % $maxCntPerPage > 0) {
			$rowNo = $rowNo + $maxCntPerPage - ($i + 1 - 1) % $maxCntPerPage + $footerCnt;
			$worksheet->getPageSetup()->setPrintArea('A1:M'.$rowNo);
			$worksheet1->getPageSetup()->setPrintArea('A1:M'.$rowNo);
			$worksheet2->getPageSetup()->setPrintArea('A1:M'.$rowNo);
		}

		// $worksheet->removeRow($rowNo + 1, 30);

		return $spreadsheet;
	}

	/**
	 * 請求書、送り状
	 */
	private function invoice($datas, $customers) {
		$reader = new XlsxReader();
		$spreadsheet = $reader->load($this->excelRoot.'請求書&送り状.xlsx');
		$worksheetCover = $spreadsheet->getSheetByName('表紙');
		$worksheet = $spreadsheet->getSheetByName('請求明細');
		$worksheet2 = $spreadsheet->getSheetByName('送り状');

		// array_shift($datas);

		// 改ページための空行追加
		if (count($datas) > 17 && count($datas) <= 20) {
			$datas[] = ["serial"=>"", "itemName"=>"", "size" => "", "quantity" => "", "unit" => "", "price" => ""];
			$datas[] = ["serial"=>"", "itemName"=>"", "size" => "", "quantity" => "", "unit" => "", "price" => ""];
			$datas[] = ["serial"=>"", "itemName"=>"", "size" => "", "quantity" => "", "unit" => "", "price" => ""];
		}

        $maxCntPerPage = 20;
		$rowNo = 10;
		for ($i = 0; $i < count($datas); $i++) {
			$row = $datas[$i];

			$worksheet->setCellValue('A'.($rowNo), $row["serial"]);
			$worksheet->setCellValue('B'.($rowNo), $row["itemName"]);
			$worksheet->setCellValue('C'.($rowNo), $row["size"]);
			$worksheet->setCellValue('D'.($rowNo), $row["quantity"]);
			$worksheet->setCellValue('E'.($rowNo), $row["unit"]);
			$worksheet->setCellValue('F'.($rowNo), $row["price"]);
			$worksheet->setCellValue('G'.($rowNo), "=D".$rowNo."*F".$rowNo);

			$rowNo++;

			if (($i + 1) % $maxCntPerPage === 0) {
				$worksheet->getPageSetup()->setPrintArea('A1:J'.$rowNo);
				$worksheet2->getPageSetup()->setPrintArea('A1:J'.$rowNo);
				$worksheetCover->setCellValue('C12', '=請求明細!G'.($rowNo - 3));

				$rowNo += 10;
			}
		}

		// 取引先名
		$worksheet->setCellValue('B4', $customers[0]['name']);
		
        $rowNo--;
        $footerCnt = 1;
		if ($i > 0 && ($i + 1 - 1) % $maxCntPerPage > 0) {
			$rowNo = $rowNo + $maxCntPerPage - ($i + 1 - 1) % $maxCntPerPage + $footerCnt;
			$worksheet->getPageSetup()->setPrintArea('A1:J'.$rowNo);
			$worksheet2->getPageSetup()->setPrintArea('A1:J'.$rowNo);
			$worksheetCover->setCellValue('C12', '=請求明細!G'.($rowNo - 3));
		}

		return $spreadsheet;
	}

	/**
	 * 注文書
	 */
	private function order($datas, $customers) {
		$reader = new XlsxReader();
		$spreadsheet = $reader->load($this->excelRoot.'注文書.xlsx');
		$worksheet = $spreadsheet->getSheetByName('注文書');

		$rowNo = 10;
		$num = 1;
		$kingakuSum = 0;
		for ($i = 0; $i < count($datas); $i++) {
			$row = $datas[$i];

			$worksheet->setCellValue('A'.($rowNo), $num++);
			$worksheet->setCellValue('B'.($rowNo), $row["itemName"]);
			$worksheet->setCellValue('C'.($rowNo), $row["size"]);
			$worksheet->setCellValue('D'.($rowNo), $row["quantity"]);
			$worksheet->setCellValue('E'.($rowNo), $row["unit"]);
			$worksheet->setCellValue('F'.($rowNo), $row["price"]);

			$rowNo++;

			if ($i > 0 && ($i + 1) % 20 === 0) {
				$worksheet->getPageSetup()->setPrintArea('A1:J'.$rowNo);
				$rowNo += 11;
				$kingakuSum = 0;
			}
		}

		// 取引先名
		$worksheet->setCellValue('B4', $customers[0]['name']);

		if ($i > 0 && ($i + 1 - 1) % 20 > 0) {
			$rowNo = $rowNo + 20 - ($i + 1 - 1) % 20 + 1;
			$worksheet->getPageSetup()->setPrintArea('A1:J'.$rowNo);
		}

		return $spreadsheet;
	}

	/**
	 * 出荷証明書
	 */
	private function shipping($datas, $customers) {
		$reader = new XlsxReader();
		$spreadsheet = $reader->load($this->excelRoot.'出荷証明書.xlsx');
		$worksheet = $spreadsheet->getSheetByName('出荷証明書');

		$rowNo = 18;
        $pageCnt = 16;
		for ($i = 0; $i < count($datas); $i++) {
			$row = $datas[$i];

			$worksheet->setCellValue('A'.($rowNo), $row["itemName"]);
			$worksheet->setCellValue('E'.($rowNo), $row["size"]);
			$worksheet->setCellValue('G'.($rowNo), $row["quantity"]);

			$rowNo++;

			if ($i > 0 && ($i + 1) % $pageCnt === 0) {
				$worksheet->getPageSetup()->setPrintArea('A1:L'.$rowNo);
				$rowNo += 24;
			}
		}

		// 取引先名
		$worksheet->setCellValue('A6', $customers[0]['name']);

        $rowNo--;
        $footerCnt = 7;
		if ($i > 0 && ($i + 1 - 1) % $pageCnt > 0) {
			$rowNo = $rowNo + ($pageCnt - ($i + 1 - 1) % $pageCnt) + $footerCnt;
			$worksheet->getPageSetup()->setPrintArea('A1:L'.$rowNo);
		}

		return $spreadsheet;
	}
    
	/**
	 * 見積依頼書
	 */
	private function estimateRequest($datas, $customer) {

		$reader = new XlsxReader();
		$spreadsheet = $reader->load($this->excelRoot.'見積依頼書.xlsx');
		$worksheet = $spreadsheet->getSheetByName('見積依頼書');

		$rowNo = 8;
		foreach ($datas as $row) {

			$worksheet->setCellValue('A'.($rowNo), $row["itemName"]);
			$worksheet->setCellValue('C'.($rowNo), $row["size"]);
			$worksheet->setCellValue('D'.($rowNo), $row["quantity"]);
			$worksheet->setCellValue('E'.($rowNo), $row["unit"]);
			$worksheet->setCellValue('F'.($rowNo), $row["price"]);

			$rowNo++;
		}

		$worksheet->setCellValue('B4', $customer['name']);

		$worksheet->removeRow($rowNo, 95 - $rowNo);

		return $spreadsheet;
	}


	// /**
	//  * 請求書
	//  */
	// private function invoice($datas) {

	// 	$reader = new XlsxReader();
	// 	$spreadsheet = $reader->load($this->excelRoot.'請求書.xlsx');
	// 	$worksheet = $spreadsheet->getSheetByName('請求内訳');
	// 	// $sheetTem = $spreadsheet->getSheetByName('sheet');

	// 	$rowNo = 22;
	// 	foreach ($datas as $row) {
	// 		for ($i = 0; $i < 9; $i++) {

	// 			$worksheet->setCellValue('B'.($rowNo), $row["itemName"]);
	// 			$worksheet->setCellValue('D'.($rowNo), $row["size"]);
	// 			$worksheet->setCellValue('E'.($rowNo), $row["quantity"]);
	// 			$worksheet->setCellValue('F'.($rowNo), $row["unit"]);
	// 			$worksheet->setCellValue('G'.($rowNo), $row["price"]);

	// 			if ($rowNo == 66 || 
	// 				($rowNo > 66 && ($rowNo - 66) % 58 == 0) ) {

	// 				$worksheet->setBreak("A".$rowNo, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
	// 				$rowNo++;
	// 			}
	// 			$rowNo++;
	// 		}
	// 	}

	// 	if ($rowNo <= 66) {
	// 		$lastRow = 66;
	// 	} else {
	// 		$lastRow = $rowNo + (58 - ($rowNo - 66) % 58);
	// 	}

	// 	// 合計行
	// 	$this->copyRows($worksheet, "A".Consts::ESTIMATE_TOTAL_ROWNO.":K".Consts::ESTIMATE_TOTAL_ROWNO, $worksheet, "A".$lastRow);
	// 	$worksheet->getPageSetup()->setPrintArea('A1:K'.$lastRow);
	// 	// 金額合計
	// 	$worksheet->setCellValue('H'.$lastRow, "=SUM(H22:H".($lastRow - 1).")");

	// 	// 表紙
	// 	$coversheet = $spreadsheet->getSheetByName('請求印無');
	// 	$coversheet->setCellValue('C12', '=請求内訳!H'.$lastRow);
	// 	$coversheet->setSelectedCells('A1:A1');

	// 	// remove sheet
	// 	// $sheetIndex = $spreadsheet->getIndex($sheetTem);
	// 	// $spreadsheet->removeSheetByIndex($sheetIndex);			

	// 	return $spreadsheet;
	// }


	public function copyRows(Worksheet $sheet, string $fromAddress, Worksheet $dustSheet, string $dustAddress)
    {
        //コピー元のセル値を配列で取得
        $range = $sheet->rangeToArray($fromAddress);

        //コピー先に値を貼り付け
        $dustSheet->fromArray($range, null, $dustAddress);

        //コピー元セル範囲の開始、終了位置をインデックスで取得
        [$start, $end] = Coordinate::rangeBoundaries($fromAddress);
        //コピー先セルの位置をインデックスで取得
        $dustRow = (int)Coordinate::rangeBoundaries($dustAddress)[0][1];

        //書式のコピー
        foreach (range((int)$start[1], (int)$end[1]) as $index => $row) {
            foreach (range($start[0], $end[0]) as $col) {
                //コピー元のセルスタイル取得
                $style = $sheet->getStyleByColumnAndRow($col, $row);
                //コピー先のセルにスタイルをコピー
                $dustSheet->duplicateStyle($style,
                    Coordinate::stringFromColumnIndex($col).strval($dustRow + $index));
            }
        }
    }	

}
