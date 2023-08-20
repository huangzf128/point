<?php

namespace App\Action;

use Exception;
use PDO;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Container\ContainerInterface;
use App\Common\Consts;
use App\Common\Util;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Exception as WriterException;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use App\Model\ItemModel;
use App\Model\CustomerModel;

/**
 * Action
 */
final class ImportAction extends BaseAction
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

	public function index(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
		$errMsg = $this->getFlash('errMsg', true);
		return $this->container->get('view')->render($response, 'import.html', ['errMsg' => $errMsg]);
	}

	public function item(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();

		$imgPath = "";
		if ($_FILES['itemFile']['error'] === UPLOAD_ERR_OK) {
			$excel = $_FILES["itemFile"]["tmp_name"];

			$reader = new XlsxReader();
			$spreadsheet = $reader->load($excel);
			$worksheet = $spreadsheet->getSheetByName('2022年');

			$datas = [];

			$lowRow = 6;
			$highestRow = $worksheet->getHighestRow();
			
			for ($i = $lowRow; $i <= $highestRow; $i++) {
				$serial = $worksheet->getCell('D'.($i))->getValue();
				$name = $worksheet->getCell('A'.($i))->getValue();
				$size = $worksheet->getCell('E'.($i))->getValue();

				if (!empty($serial) && !array_key_exists($serial, $datas)) {
					$datas[$serial] = [$name, $size];
				}
			}

			$model = new ItemModel($this->container);
			foreach($datas as $key => $value) {
				$this->trimArray($value);
				$model->insertItem(["serial" => $key, "itemName" => $value[0], "size" => $value[1]]);
			}

		}
		return $response->withHeader('Location', '/import')->withStatus(302);
	}

	public function customer(ServerRequestInterface $request, ResponseInterface $response): ResponseInterface
    {
        $parsedBody = $request->getParsedBody();

		$imgPath = "";
		if ($_FILES['customerFile']['error'] === UPLOAD_ERR_OK) {
			$excel = $_FILES["customerFile"]["tmp_name"];

			$reader = new XlsxReader();
			$spreadsheet = $reader->load($excel);
			$model = new CustomerModel($this->container);

			$sheetNames = $spreadsheet->getSheetNames();
			foreach($sheetNames as $sheetName) {
				$worksheet = $spreadsheet->getSheetByName($sheetName);
				$datas = [];
				$kana = "";
				$lowRow = 2;
				$highestRow = $worksheet->getHighestRow();
				
				for ($i = $lowRow; $i <= $highestRow; $i++) {
					$name = $worksheet->getCell('B'.($i))->getValue();
	
					if (empty($name)) {
						continue;
					}
	
					if (mb_strlen($name) == 2 && mb_substr($name, 1, 1) == "行") {
						$kana = mb_substr($name, 0, 1);
						continue;
					}
	
					$tel = $worksheet->getCell('C'.($i))->getValue();
					$fax = $worksheet->getCell('D'.($i))->getValue();
					$postcode = $worksheet->getCell('E'.($i))->getValue();
					$address = $worksheet->getCell('F'.($i))->getValue();
					$managerName = $worksheet->getCell('G'.($i))->getValue();
					$remark = $worksheet->getCell('H'.($i))->getValue();
	
					$datas[] = ['name' => $name, 'kana' => $kana, 'managerName' => $managerName, 'address' => $address, 'remark' => $remark,
								'tel' => $tel, 'fax' => $fax, 'postcode' => $postcode];
				}
	
				foreach($datas as $value) {
					$value['tel'] = Util::sanitizeTelorPost($value['tel']);
					$value['fax'] = Util::sanitizeTelorPost($value['fax']);
					$value['postcode'] = Util::sanitizeTelorPost($value['postcode']);
					$this->trimArray($value);
					$model->insertCustomer($value);
				}
			}

		}

		$this->saveToFlash("errMsg", "取込が完了しました。");
		return $response->withHeader('Location', '/import')->withStatus(302);
	}

	private function trimArray($ary) {
		foreach($ary as $key => $value) {
			$ary[$key] = trim($value ?? "");
		}
	}

}
