<?php

namespace CorporateTrainingBundle\Biz\Exporter;

use Symfony\Component\HttpFoundation\StreamedResponse;

class Excel
{
    protected $phpExcelIO;

    public function __construct($phpExcelIO = '\PHPExcel_IOFactory')
    {
        $this->phpExcelIO = $phpExcelIO;
    }

    public function createPHPExcelObject($filename = null)
    {
        return (null === $filename) ? new \PHPExcel() : call_user_func(array($this->phpExcelIO, 'load'), $filename);
    }

    public function createWriter(\PHPExcel $phpExcelObject, $type = 'Excel5')
    {
        return call_user_func(array($this->phpExcelIO, 'createWriter'), $phpExcelObject, $type);
    }

    public function createStreamedResponse(\PHPExcel_Writer_IWriter $writer, $status = 200, $headers = array())
    {
        return new StreamedResponse(
            function () use ($writer) {
                $writer->save('php://output');
            },
            $status,
            $headers
        );
    }
}
