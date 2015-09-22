<?php
namespace console\controllers;

use common\components\zContractParser\zContractParser;
use Yii;
use yii\base\ErrorException;
use yii\helpers\BaseFileHelper;
use yii\console\Controller;

class ParseController extends Controller
{
    protected $tempDir = 'temp_archives';

    public function actionIndex()
    {
        $path = Yii::$app->getRuntimePath() . '/archives';

        $archivesList = BaseFileHelper::findFiles($path,[
            'only' => ['*.zip'],
            'recursive' => false,
        ]);
        $zip = new \ZipArchive();
        foreach ($archivesList as $archivePath) {
            try {
                $zipOpened = $zip->open($archivePath);
            } catch (ErrorException $e) {
                print $e->getMessage() . PHP_EOL;
                continue;
            }
            if ($zipOpened !== true) {
                continue;
            }
            //$date = \DateTime::createFromFormat()
            $numFiles = $zip->numFiles;
            for ($i=0; $i<$numFiles; $i++) {

                $xmlFilename = $zip->getNameIndex($i);
                $type = explode('_',$xmlFilename)[0];
                if ($type !== 'contract') {
                    continue;
                }

                $contractParser = new zContractParser($zip->getFromIndex($i));
                $isBuilder = $contractParser->isBuilding();

                $suppliers = $contractParser->getSuppliersInfo();
                if ($suppliers === false || count($suppliers) < 1) {
//                    print '---------------' . $xmlFilename . '---------------'.PHP_EOL;
//                    print 'No suppliers: ' . basename($archivePath) . PHP_EOL;
//                    print '-----------------------' . PHP_EOL;
                    continue;
                }
                if (empty($suppliers[0]['type'])) {
                    var_dump($suppliers[0]['type']);
                }
            }
        }
        return Controller::EXIT_CODE_NORMAL;
    }
}