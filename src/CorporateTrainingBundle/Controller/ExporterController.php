<?php

namespace CorporateTrainingBundle\Controller;

use AppBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExporterController extends BaseController
{
    public function exporterAction(Request $request, $type, $formSubmission)
    {
        $exportFactory = $this->getBiz()->offsetGet('export_factory');
        $exporter = $exportFactory->create($type);
        $exporter->setServiceContainer($this->container);

        if ('post' == $formSubmission) {
            $parameters = $request->request->all();
        } else {
            $parameters = $request->query->all();
        }

        if (!$exporter->canExport($parameters)) {
            throw $this->createAccessDeniedException();
        }

        $response = $this->createStreamedResponse($exporter->writeToExcel($parameters));
        $exportFileName = $exporter->getExportFileName();
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $exportFileName
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }

    protected function createStreamedResponse(\PHPExcel_Writer_IWriter $writer, $status = 200, $headers = array())
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
