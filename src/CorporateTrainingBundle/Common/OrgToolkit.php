<?php

namespace CorporateTrainingBundle\Common;

use AppBundle\Common\ArrayToolkit;

class OrgToolkit
{
    public static function buildOrgsNames(array $orgIds, array $orgs, $delimiter = '/')
    {
        $orgsNames = '';

        if (empty($orgIds) || empty($orgs)) {
            return $orgsNames;
        }

        $orgs = ArrayToolkit::index($orgs, 'id');

        foreach ($orgIds as $orgId) {
            if (!empty($orgs[$orgId])) {
                $orgsNames = $orgsNames.$delimiter.$orgs[$orgId]['name'];
            }
        }

        return trim($orgsNames, $delimiter);
    }

    public static function buildOrgsNamesAndCodes(array $orgIds, array $orgs)
    {
        $orgsNames = array();

        if (empty($orgIds) || empty($orgs)) {
            return $orgsNames;
        }

        $orgs = ArrayToolkit::index($orgs, 'id');

        foreach ($orgIds as $orgId) {
            if (!empty($orgs[$orgId])) {
                $org['name'] = $orgs[$orgId]['name'];
                $org['code'] = $orgs[$orgId]['code'];
                $orgsNames[] = $org;
            }
        }

        return $orgsNames;
    }
}
