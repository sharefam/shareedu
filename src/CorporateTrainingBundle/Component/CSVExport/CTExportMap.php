<?php

namespace CorporateTrainingBundle\Component\CSVExport;

class CTExportMap
{
    public function getMap()
    {
        return array(
            'ct:data_center_user_detail' => 'CorporateTrainingBundle\Component\CSVExport\Admin\DataCenterUserDetailExporter',
        );
    }
}
